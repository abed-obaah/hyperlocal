const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');
const mysql = require('mysql2/promise');
const crypto = require('crypto');

// Load environment variables from the Laravel .env file
require('dotenv').config({ path: __dirname + '/.env' });

const app = express();
app.use(cors());
app.use(express.json());

const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: '*',
    methods: ['GET', 'POST']
  }
});

// Configure MySQL Database Connection Pool
const pool = mysql.createPool({
  host: process.env.DB_HOST || '127.0.0.1',
  port: process.env.DB_PORT || 3306,
  user: process.env.DB_USERNAME || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_DATABASE || 'hyperlocal',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
});

// Authentication Middleware via Sanctum Personal Access Token
io.use(async (socket, next) => {
  try {
    const token = socket.handshake.auth?.token || socket.handshake.query?.token;
    if (!token) {
      return next(new Error('Authentication error: Token missing'));
    }

    // Clean Bearer prefix
    const cleanToken = token.startsWith('Bearer ') ? token.slice(7) : token;

    let plainTextToken = cleanToken;
    let tokenId = null;

    // Sanctum tokens format: "id|plainTextToken"
    if (cleanToken.includes('|')) {
      const parts = cleanToken.split('|');
      tokenId = parts[0];
      plainTextToken = parts[1];
    }

    const hashedToken = crypto.createHash('sha256').update(plainTextToken).digest('hex');

    let query = 'SELECT * FROM personal_access_tokens WHERE token = ?';
    let params = [hashedToken];
    if (tokenId) {
      query = 'SELECT * FROM personal_access_tokens WHERE id = ? AND token = ?';
      params = [tokenId, hashedToken];
    }

    const [tokens] = await pool.execute(query, params);
    if (tokens.length === 0) {
      return next(new Error('Authentication error: Invalid token'));
    }

    const tokenRecord = tokens[0];
    const [users] = await pool.execute('SELECT * FROM users WHERE id = ?', [tokenRecord.tokenable_id]);
    if (users.length === 0) {
      return next(new Error('Authentication error: User not found'));
    }

    socket.user = users[0];
    next();
  } catch (err) {
    next(new Error('Authentication error: ' + err.message));
  }
});

io.on('connection', (socket) => {
  console.log(`User connected: ${socket.user.name} (Role: ${socket.user.role})`);

  // Room subscription
  socket.on('join_delivery_tracking', async (payload) => {
    try {
      const deliveryId = payload?.deliveryId;
      if (!deliveryId) return;

      const [deliveries] = await pool.execute('SELECT * FROM deliveries WHERE id = ?', [deliveryId]);
      if (deliveries.length === 0) return;
      const delivery = deliveries[0];

      let isAllowed = false;
      if (socket.user.role === 'admin') {
        isAllowed = true;
      } else if (socket.user.role === 'rider' && Number(delivery.rider_id) === Number(socket.user.id)) {
        isAllowed = true;
      } else if (socket.user.role === 'customer') {
        const [orders] = await pool.execute('SELECT * FROM orders WHERE id = ?', [delivery.order_id]);
        if (orders.length > 0 && Number(orders[0].customer_id) === Number(socket.user.id)) {
          isAllowed = true;
        }
      }

      if (isAllowed) {
        socket.join(`delivery:${deliveryId}`);
        console.log(`Socket ${socket.id} joined room: delivery:${deliveryId}`);
      }
    } catch (err) {
      console.error('Error in join_delivery_tracking:', err);
    }
  });

  // Client updates location via websocket directly
  socket.on('rider_location_update', async (payload) => {
    try {
      const { delivery_id, latitude, longitude, heading, speed, accuracy } = payload || {};
      if (!delivery_id || !latitude || !longitude) return;

      const [deliveries] = await pool.execute('SELECT * FROM deliveries WHERE id = ?', [delivery_id]);
      if (deliveries.length === 0) return;
      const delivery = deliveries[0];

      if (Number(delivery.rider_id) !== Number(socket.user.id)) {
        return;
      }

      const now = new Date();
      await pool.execute(
        'INSERT INTO rider_locations (rider_id, delivery_id, latitude, longitude, heading, speed, accuracy, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [socket.user.id, delivery_id, latitude, longitude, heading || null, speed || null, accuracy || null, now, now]
      );

      await pool.execute(
        'UPDATE users SET current_latitude = ?, current_longitude = ?, updated_at = ? WHERE id = ?',
        [latitude, longitude, now, socket.user.id]
      );

      const updatePayload = {
        delivery_id: Number(delivery_id),
        rider_id: Number(socket.user.id),
        latitude: Number(latitude),
        longitude: Number(longitude),
        heading: heading ? Number(heading) : null,
        speed: speed ? Number(speed) : null,
        accuracy: accuracy ? Number(accuracy) : null,
        updated_at: now.toISOString()
      };

      io.to(`delivery:${delivery_id}`).emit('rider_location_updated', updatePayload);
      console.log(`Websocket broadcast location update for delivery:${delivery_id}`);
    } catch (err) {
      console.error('Error on rider_location_update:', err);
    }
  });

  socket.on('disconnect', () => {
    console.log(`User disconnected: ${socket.user.name}`);
  });
});

// REST Endpoint to trigger socket broadcast from Laravel
app.post('/api/broadcast', (req, res) => {
  try {
    const { delivery_id, rider_id, latitude, longitude, heading, speed, accuracy, updated_at } = req.body;
    if (!delivery_id || !latitude || !longitude) {
      return res.status(400).json({ error: 'Missing required parameters' });
    }

    const updatePayload = {
      delivery_id: Number(delivery_id),
      rider_id: Number(rider_id),
      latitude: Number(latitude),
      longitude: Number(longitude),
      heading: heading ? Number(heading) : null,
      speed: speed ? Number(speed) : null,
      accuracy: accuracy ? Number(accuracy) : null,
      updated_at: updated_at || new Date().toISOString()
    };

    io.to(`delivery:${delivery_id}`).emit('rider_location_updated', updatePayload);
    console.log(`HTTP broadcast location update to delivery:${delivery_id}`);
    return res.json({ success: true });
  } catch (err) {
    console.error('HTTP broadcast error:', err);
    return res.status(500).json({ error: err.message });
  }
});

const PORT = process.env.SOCKET_PORT || 3001;
server.listen(PORT, () => {
  console.log(`Socket.IO Server running on port ${PORT}`);
});
