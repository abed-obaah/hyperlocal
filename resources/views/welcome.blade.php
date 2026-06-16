@verbatim
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hyperlocal — Order food. Get it fast.</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    :root{
      /* Palette matched to the mobile app (tailwind primary scale) */
      --green:#0B3D2E; --green-mid:#137A4F; --green-bright:#2E8B62; --green-deep:#06251C;
      --soft:#E8F1EC; --soft-2:#C5DDD2;
      --dark:#06251C; --ink:#16201B; --muted:#5C6B63;
      --page:#F4F5F7; --white:#fff;
    }
    *{box-sizing:border-box;margin:0;padding:0}
    html{scroll-behavior:smooth}
    body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:var(--page);color:var(--ink);line-height:1.55;-webkit-font-smoothing:antialiased}
    a{text-decoration:none;color:inherit}
    img{display:block;max-width:100%}
    .wrap{max-width:1180px;margin:0 auto;padding:0 22px}
    .btn{display:inline-flex;align-items:center;gap:8px;font-weight:700;border-radius:999px;padding:14px 26px;font-size:15px;border:none;cursor:pointer;transition:.18s}
    .btn-white{background:#fff;color:var(--green)}
    .btn-white:hover{transform:translateY(-1px)}
    .btn-ghost{background:transparent;border:1.6px solid rgba(255,255,255,.55);color:#fff}
    .btn-ghost:hover{background:rgba(255,255,255,.12)}
    .btn-green{background:var(--green);color:#fff}
    .btn-green:hover{background:#0F5F3D}
    .btn-dark{background:var(--green-deep);color:#fff}
    .eyebrow{color:var(--green-mid);font-weight:800;font-size:13px;letter-spacing:1.5px;text-transform:uppercase}

    /* ---------- Phone mockup ---------- */
    .phone{width:212px;background:#14151d;border-radius:34px;padding:9px;box-shadow:0 30px 60px rgba(0,0,0,.30);position:relative}
    .phone .notch{position:absolute;top:16px;left:50%;transform:translateX(-50%);width:74px;height:7px;border-radius:99px;background:#000;opacity:.55;z-index:2}
    .phone .screen{background:#fff;border-radius:26px;overflow:hidden;height:400px;position:relative}
    .scr-pad{padding:26px 14px 14px}
    .scr-top{display:flex;justify-content:space-between;font-size:10px;color:#8a8f98;font-weight:700;margin-bottom:10px}
    .search{background:#f1f2f4;border-radius:12px;height:34px;display:flex;align-items:center;padding:0 12px;color:#9aa0a8;font-size:11px;gap:6px}
    .promo{margin-top:12px;background:linear-gradient(120deg,var(--green-bright),var(--green));border-radius:16px;padding:14px;color:#fff;position:relative;overflow:hidden}
    .promo h5{font-size:14px;font-weight:800;line-height:1.2}
    .promo .chip{display:inline-block;margin-top:10px;background:#fff;color:var(--green);font-size:10px;font-weight:800;border-radius:99px;padding:5px 10px}
    .promo .ph-food{position:absolute;right:-6px;bottom:-6px;width:74px;height:74px;border-radius:16px;background:#fff3 center/cover;border:3px solid #ffffff55}
    .cats{display:flex;justify-content:space-between;margin:14px 2px}
    .cats div{width:42px;text-align:center;font-size:10px;color:#6f7682}
    .cats .ic{width:42px;height:42px;border-radius:14px;background:var(--soft);display:grid;place-items:center;font-size:16px;font-weight:800;color:var(--green-mid);margin-bottom:4px}
    .scr-label{font-size:12px;font-weight:800;margin:4px 2px 8px}
    .fitem{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid #f0f1f3;border-radius:14px;padding:8px;margin-bottom:8px}
    .fitem .t{width:44px;height:44px;border-radius:10px;background:#e9eef0 center/cover}
    .fitem .nm{font-size:11px;font-weight:800}
    .fitem .pr{font-size:11px;color:var(--green-mid);font-weight:800}
    /* map phone */
    .map{position:absolute;inset:0;background:
        repeating-linear-gradient(0deg,#eef1f3 0 1px,transparent 1px 26px),
        repeating-linear-gradient(90deg,#eef1f3 0 1px,transparent 1px 26px),#f6f8f9}
    .map .route{position:absolute;inset:0}
    .pin{position:absolute;width:13px;height:13px;border-radius:50%;box-shadow:0 4px 6px rgba(0,0,0,.2)}
    .pin.start{background:var(--ink)}
    .pin.end{background:var(--green-mid);box-shadow:0 0 0 5px rgba(19,122,79,.28)}
    .map-card{position:absolute;left:12px;right:12px;bottom:12px;background:#fff;border-radius:16px;padding:12px;box-shadow:0 12px 24px rgba(0,0,0,.12)}
    .map-card .t{font-size:12px;font-weight:800}
    .map-card .s{font-size:10px;color:#6f7682}
    .map-top{position:absolute;left:12px;right:12px;top:30px;background:#fff;border-radius:12px;padding:8px 12px;font-size:11px;font-weight:800;box-shadow:0 8px 18px rgba(0,0,0,.1);display:flex;align-items:center;gap:8px}

    /* ---------- Hero ---------- */
    .hero{
      background:
        radial-gradient(58% 46% at 50% 116%, rgba(255,255,255,.14), transparent 70%),
        radial-gradient(48% 55% at 4% 54%, rgba(255,255,255,.08), transparent 60%),
        radial-gradient(48% 55% at 96% 46%, rgba(255,255,255,.06), transparent 60%),
        radial-gradient(135% 95% at 75% -18%, #237e57 0%, var(--green) 52%, var(--green-deep) 120%);
      border-radius:30px;color:#fff;margin:18px;padding-bottom:0;position:relative;overflow:hidden}
    nav{display:flex;align-items:center;justify-content:space-between;padding:22px 0}
    .logo{display:inline-flex;align-items:center}
    .logo img{display:block;height:30px;width:auto}
    .logo-chip{background:#fff;border-radius:12px;padding:7px 12px;display:inline-flex;align-items:center;box-shadow:0 6px 16px rgba(0,0,0,.12)}
    .nav-links{display:flex;gap:28px;font-weight:600;font-size:14px;color:rgba(255,255,255,.9)}
    .nav-links a:hover{color:#fff;text-decoration:underline}
    .hero-head{text-align:center;max-width:640px;margin:26px auto 0}
    .hero-head h1{font-size:54px;line-height:1.04;font-weight:800;letter-spacing:-1.5px}
    .hero-head p{margin:18px auto 26px;font-size:17px;color:rgba(255,255,255,.88);max-width:520px}
    .hero-cta{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}
    /* Two phones overlapping, fanned apart, rising from the clipped bottom edge. */
    .phones{display:flex;justify-content:center;align-items:flex-end;margin-top:40px;margin-bottom:-104px}
    .phones .phone{width:222px}
    .phones .p-a{transform:rotate(-9deg) translateY(14px);z-index:2;margin-right:-46px}
    .phones .p-b{transform:rotate(9deg) translateY(-28px);z-index:1}

    /* ---------- Stats ---------- */
    .stats-wrap{margin:-46px 22px 0;position:relative;z-index:5}
    .stats{max-width:1000px;margin:0 auto;background:var(--dark);border-radius:20px;display:grid;grid-template-columns:repeat(4,1fr);padding:26px 18px;box-shadow:0 24px 50px rgba(0,0,0,.18)}
    .stat{text-align:center;color:#fff;border-right:1px solid rgba(255,255,255,.12)}
    .stat:last-child{border-right:none}
    .stat .n{font-size:28px;font-weight:800}
    .stat .l{font-size:12px;color:rgba(255,255,255,.6)}

    /* ---------- Feature sections ---------- */
    section.feat{padding:86px 0}
    .feat-grid{display:grid;grid-template-columns:1fr 1fr;gap:50px;align-items:center}
    .feat h2{font-size:36px;font-weight:800;letter-spacing:-1px;margin:12px 0 16px}
    .feat p{color:var(--muted);font-size:15px}
    .blob{position:relative;display:flex;justify-content:center}
    .blob::before{content:"";position:absolute;width:280px;height:300px;background:linear-gradient(150deg,var(--green-bright),var(--green-mid));border-radius:40px 40px 44px 44px;transform:rotate(-3deg);box-shadow:0 24px 50px rgba(11,61,46,.30)}
    .blob .phone{position:relative;z-index:2}

    /* ---------- Testimonials ---------- */
    .tcenter{text-align:center;max-width:560px;margin:0 auto 44px}
    .tcenter h2{font-size:36px;font-weight:800;letter-spacing:-1px;margin-top:8px}
    .tgrid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
    .tcard{border-radius:20px;padding:24px}
    .tcard.full{background:linear-gradient(135deg,var(--green-mid),var(--green));color:#fff}
    .tcard.soft{background:var(--soft);color:var(--ink)}
    .tcard .who{display:flex;align-items:center;gap:12px;margin-bottom:12px}
    .tcard .who img{width:44px;height:44px;border-radius:50%;background:#fff}
    .tcard .nm{font-weight:800;font-size:15px}
    .tcard .ro{font-size:12px;opacity:.8}
    .tcard p{font-size:14px}
    .tcard.full p{color:rgba(255,255,255,.92)}
    .tcard.soft p{color:var(--muted)}

    /* ---------- Download CTA ---------- */
    .download{margin:0 22px;background:radial-gradient(120% 120% at 90% 10%,#2E8B62,var(--green) 60%,var(--green-deep));border-radius:30px;color:#fff;overflow:hidden}
    .dl-grid{display:grid;grid-template-columns:1fr 1fr;align-items:center;gap:20px}
    .dl-text{padding:60px 0 60px 14px}
    .dl-text .badge{width:54px;height:54px;border-radius:16px;background:#fff;color:var(--green);display:grid;place-items:center;font-size:24px;font-weight:800;margin-bottom:18px}
    .dl-text h2{font-size:40px;font-weight:800;letter-spacing:-1px;line-height:1.05}
    .dl-img{height:340px;background:#0F5F3D center/cover;border-radius:24px}

    /* ---------- Footer ---------- */
    footer{background:var(--dark);color:rgba(255,255,255,.7);margin:60px 18px 18px;border-radius:24px;padding:54px 0 26px}
    .foot-grid{display:grid;grid-template-columns:1.6fr 1fr 1fr;gap:30px}
    footer .logo{margin-bottom:14px}
    footer h5{color:#fff;font-weight:800;font-size:14px;margin-bottom:14px}
    footer a{display:block;font-size:14px;margin-bottom:9px;color:rgba(255,255,255,.65)}
    footer a:hover{color:var(--green-bright)}
    .socials{display:flex;gap:10px;margin-top:16px}
    .socials span{min-width:36px;height:36px;padding:0 10px;border-radius:10px;background:rgba(255,255,255,.08);display:grid;place-items:center;font-size:12px;font-weight:700}
    .foot-bottom{border-top:1px solid rgba(255,255,255,.1);margin-top:36px;padding-top:20px;display:flex;justify-content:space-between;font-size:13px;flex-wrap:wrap;gap:10px}

    @media (max-width:860px){
      .hero-head h1{font-size:38px}
      .nav-links{display:none}
      .stats{grid-template-columns:repeat(2,1fr);gap:18px 0}
      .stat:nth-child(2){border-right:none}
      .feat-grid,.tgrid,.dl-grid,.foot-grid{grid-template-columns:1fr}
      .feat .order-text{order:2}
      .dl-img{height:240px;margin:0 14px 24px}
      .phones{margin-bottom:-70px}
      .phones .phone{width:158px}
      .phones .p-a{margin-right:-32px}
    }
  </style>
</head>
<body>

  <!-- ===== HERO ===== -->
  <header class="hero">
    <div class="wrap">
      <nav>
        <div class="logo logo-chip"><img src="/logo.png" alt="Hyperlocal" /></div>
        <div class="nav-links">
          <a href="#menus">Popular Menus</a>
          <a href="#track">Track Order</a>
          <a href="#feedback">Feedback</a>
          <a href="#download">Get the App</a>
        </div>
        <a class="btn btn-dark" href="/dashboard">Log in</a>
      </nav>

      <div class="hero-head">
        <h1>Order food.<br>Get it fast.<br>Built for your city.</h1>
        <p>Hyperlocal connects you to the best restaurants in Abraka and Warri — real menus, real prices, real delivery. No middleman. No Lagos pricing.</p>
        <div class="hero-cta">
          <a class="btn btn-white" href="#menus">Order Now</a>
          <a class="btn btn-ghost" href="#menus">Browse Restaurants</a>
        </div>
      </div>

      <div class="phones">
        <!-- Food list phone -->
        <div class="phone p-a">
          <div class="notch"></div>
          <div class="screen">
            <div class="scr-pad">
              <div class="scr-top"><span>9:41</span><span>5G</span></div>
              <div class="search">Search food, drinks…</div>
              <div class="promo">
                <h5>Fast delivery<br>10% off today</h5>
                <span class="chip">Order now</span>
                <div class="ph-food" style="background-image:url('https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=200&q=80&auto=format&fit=crop')"></div>
              </div>
              <div class="cats">
                <div><div class="ic">B</div>Burgers</div>
                <div><div class="ic">P</div>Pizza</div>
                <div><div class="ic">S</div>Salads</div>
                <div><div class="ic">G</div>Grill</div>
              </div>
              <div class="scr-label">Popular near you</div>
              <div class="fitem"><div class="t" style="background-image:url('https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=160&q=80&auto=format&fit=crop')"></div><div style="flex:1"><div class="nm">Grilled Chicken Bowl</div><div style="font-size:9px;color:#9aa0a8">4.8 · 20 min</div></div><div class="pr">$10.50</div></div>
              <div class="fitem"><div class="t" style="background-image:url('https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=160&q=80&auto=format&fit=crop')"></div><div style="flex:1"><div class="nm">Avocado Power Salad</div><div style="font-size:9px;color:#9aa0a8">4.9 · 15 min</div></div><div class="pr">$8.00</div></div>
            </div>
          </div>
        </div>
        <!-- Map phone -->
        <div class="phone p-b">
          <div class="notch"></div>
          <div class="screen">
            <div class="map">
              <svg class="route" viewBox="0 0 200 400" preserveAspectRatio="none">
                <path d="M40 300 C 90 250, 70 160, 150 110" fill="none" stroke="#137A4F" stroke-width="5" stroke-linecap="round" stroke-dasharray="2 12"/>
              </svg>
              <span class="pin start" style="left:32px;top:286px"></span>
              <span class="pin end" style="left:146px;top:92px"></span>
              <div class="map-top">Your order is on the way</div>
              <div class="map-card"><div class="t">Arriving in 12 min</div><div class="s">Daniel is 1.2 km away</div></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- ===== STATS ===== -->
  <div class="stats-wrap">
    <div class="stats">
      <div class="stat"><div class="n">8+</div><div class="l">Local kitchens</div></div>
      <div class="stat"><div class="n">1,000+</div><div class="l">Orders delivered</div></div>
      <div class="stat"><div class="n">4.8</div><div class="l">Customer rating</div></div>
      <div class="stat"><div class="n">20 min</div><div class="l">Avg delivery</div></div>
    </div>
  </div>

  <!-- ===== POPULAR MENUS ===== -->
  <section class="feat" id="menus">
    <div class="wrap feat-grid">
      <div class="blob">
        <div class="phone">
          <div class="notch"></div>
          <div class="screen">
            <div class="scr-pad">
              <div class="scr-top"><span>9:41</span><span>5G</span></div>
              <div class="search">What are you craving?</div>
              <div class="promo"><h5>Free delivery<br>on your 1st order</h5><span class="chip">Use HYPERLOCAL</span><div class="ph-food" style="background-image:url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=200&q=80&auto=format&fit=crop')"></div></div>
              <div class="scr-label" style="margin-top:14px">Popular Menu</div>
              <div class="fitem"><div class="t" style="background-image:url('https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=160&q=80&auto=format&fit=crop')"></div><div style="flex:1"><div class="nm">Smash Burger Combo</div><div style="font-size:9px;color:#9aa0a8">Smash &amp; Grill House</div></div><div class="pr">$9.50</div></div>
              <div class="fitem"><div class="t" style="background-image:url('https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=160&q=80&auto=format&fit=crop')"></div><div style="flex:1"><div class="nm">Jollof Rice &amp; Chicken</div><div style="font-size:9px;color:#9aa0a8">Mama Ada's Kitchen</div></div><div class="pr">$8.00</div></div>
            </div>
          </div>
        </div>
      </div>
      <div>
        <span class="eyebrow">Popular menus</span>
        <h2>Popular menus near you</h2>
        <p>Discover trending dishes from neighbourhood kitchens — burgers, jollof, wood-fired pizza, fresh bowls and more. Every meal is made to order with fresh, locally-sourced ingredients and delivered while it's still hot.</p>
        <div style="margin-top:24px"><a class="btn btn-green" href="#download">Explore the menu</a></div>
      </div>
    </div>
  </section>

  <!-- ===== TRACK ORDER ===== -->
  <section class="feat" id="track" style="background:#fff;border-radius:30px;margin:0 18px">
    <div class="wrap feat-grid">
      <div class="order-text">
        <span class="eyebrow">Order tracking</span>
        <h2>Track your order in real time</h2>
        <p>From the moment a kitchen accepts your order to the second your rider pulls up, watch every step live on the map. Get notified at each stage — preparing, on the way, and delivered — so you always know exactly when to expect your food.</p>
        <div style="margin-top:24px"><a class="btn btn-green" href="#download">See how it works</a></div>
      </div>
      <div class="blob">
        <div class="phone">
          <div class="notch"></div>
          <div class="screen">
            <div class="map">
              <svg class="route" viewBox="0 0 200 400" preserveAspectRatio="none">
                <path d="M40 300 C 90 250, 70 160, 150 110" fill="none" stroke="#137A4F" stroke-width="5" stroke-linecap="round" stroke-dasharray="2 12"/>
              </svg>
              <span class="pin start" style="left:32px;top:286px"></span>
              <span class="pin end" style="left:146px;top:92px"></span>
              <div class="map-top">On the way to you</div>
              <div class="map-card"><div class="t">Arriving in 12 min</div><div class="s">Tap to call your rider</div></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== FEEDBACK ===== -->
  <section class="feat" id="feedback">
    <div class="wrap">
      <div class="tcenter">
        <span class="eyebrow">Feedback</span>
        <h2>What our customers say</h2>
      </div>
      <div class="tgrid">
        <div class="tcard full">
          <div class="who"><img src="https://i.pravatar.cc/100?img=12" alt=""><div><div class="nm">Daniel R.</div><div class="ro">Warri</div></div></div>
          <p>The smash burger arrived hot and the delivery was lightning fast. Watching it on the map is genuinely fun. Easily my go-to now.</p>
        </div>
        <div class="tcard soft">
          <div class="who"><img src="https://i.pravatar.cc/100?img=32" alt=""><div><div class="nm">Sarah L.</div><div class="ro">Abraka</div></div></div>
          <p>Authentic jollof just like home, generous portions, and always fresh. Reordering from the app takes two taps.</p>
        </div>
        <div class="tcard soft">
          <div class="who"><img src="https://i.pravatar.cc/100?img=45" alt=""><div><div class="nm">Jessica D.</div><div class="ro">Warri</div></div></div>
          <p>Healthy bowls that actually taste amazing, and they arrive on time every single time. Love the live tracking.</p>
        </div>
        <div class="tcard full">
          <div class="who"><img src="https://i.pravatar.cc/100?img=51" alt=""><div><div class="nm">Lorenzo D.</div><div class="ro">Abraka</div></div></div>
          <p>Amazing experience — food arrived quickly and delicious, and the rider even included a couple of complimentary drinks.</p>
        </div>
      </div>
      <div style="text-align:center;margin-top:28px"><a class="eyebrow" href="#" style="color:var(--green-mid)">View more</a></div>
    </div>
  </section>

  <!-- ===== DOWNLOAD CTA ===== -->
  <section id="download">
    <div class="download">
      <div class="wrap dl-grid">
        <div class="dl-text">
          <div class="badge">H</div>
          <h2>Download now<br>on your mobile</h2>
          <p style="color:rgba(255,255,255,.9);margin:14px 0 24px;max-width:360px">Order, track and reorder in seconds. Hyperlocal runs on iOS &amp; Android.</p>
          <a class="btn btn-white" href="#">Download Now</a>
        </div>
        <div class="dl-img" style="background-image:url('https://images.unsplash.com/photo-1559847844-5315695dadae?w=800&q=80&auto=format&fit=crop')"></div>
      </div>
    </div>
  </section>

  <!-- ===== FOOTER ===== -->
  <footer>
    <div class="wrap">
      <div class="foot-grid">
        <div>
          <div class="logo logo-chip" style="margin-bottom:14px"><img src="/logo.png" alt="Hyperlocal" /></div>
          <p style="font-size:14px;max-width:300px">Hyperlocal connects you to the best restaurants in Abraka and Warri — real menus, real prices, real delivery.</p>
          <div class="socials"><span>X</span><span>f</span><span>IG</span><span>in</span></div>
        </div>
        <div>
          <h5>Navigate</h5>
          <a href="#menus">Popular Menus</a>
          <a href="#track">Track Order</a>
          <a href="#feedback">Feedback</a>
          <a href="#download">Get the App</a>
        </div>
        <div>
          <h5>Company</h5>
          <a href="#">About</a>
          <a href="#">Careers</a>
          <a href="#">Blog</a>
          <a href="mailto:support@hyperlocal.test">Support</a>
        </div>
      </div>
      <div class="foot-bottom">
        <span>© 2026 Hyperlocal. All rights reserved.</span>
        <span>Terms · Privacy · Cookie Policy</span>
      </div>
    </div>
  </footer>

</body>
</html>
@endverbatim
