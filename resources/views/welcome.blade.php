@verbatim
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hyperlocal — Fuel Your Day with Flavor That Matters</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    :root{
      --green:#1d4e34; --green-dark:#163a27; --green-deep:#12301f;
      --lime:#c7e94c; --gold:#f5a623; --gold-deep:#e0930f;
      --cream:#fff8ec; --ink:#17241c; --muted:#5d6b62; --white:#fff;
    }
    *{box-sizing:border-box;margin:0;padding:0}
    html{scroll-behavior:smooth}
    body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;color:var(--ink);background:var(--cream);line-height:1.5;-webkit-font-smoothing:antialiased}
    img{display:block;max-width:100%}
    a{text-decoration:none;color:inherit}
    .wrap{max-width:1180px;margin:0 auto;padding:0 24px}
    .btn{display:inline-flex;align-items:center;gap:8px;font-weight:700;border-radius:999px;padding:14px 26px;font-size:15px;cursor:pointer;border:none;transition:.2s}
    .btn-gold{background:var(--gold);color:#3a2600}
    .btn-gold:hover{background:var(--gold-deep)}
    .btn-ghost{background:transparent;border:1.5px solid rgba(255,255,255,.5);color:#fff}
    .btn-ghost:hover{background:rgba(255,255,255,.12)}
    .btn-dark{background:var(--green);color:#fff}
    .pill{display:inline-block;background:rgba(255,255,255,.14);color:var(--lime);font-weight:700;font-size:12px;letter-spacing:.5px;border-radius:999px;padding:7px 16px;text-transform:uppercase}
    .eyebrow{display:inline-flex;align-items:center;gap:8px;color:var(--gold-deep);font-weight:700;font-size:13px;letter-spacing:1px;text-transform:uppercase}

    /* ===== Hero (green) ===== */
    .hero{background:radial-gradient(120% 90% at 80% 0%,#27613f 0%,var(--green) 45%,var(--green-deep) 100%);color:#fff;border-radius:0 0 40px 40px;padding-bottom:90px;position:relative;overflow:hidden}
    nav{display:flex;align-items:center;justify-content:space-between;padding:22px 0}
    .logo{display:flex;align-items:center;gap:10px;font-weight:800;font-size:20px;color:#fff}
    .logo .mark{width:38px;height:38px;border-radius:12px;background:var(--lime);display:grid;place-items:center;font-size:20px}
    .nav-links{display:flex;gap:30px;font-weight:600;font-size:15px;color:rgba(255,255,255,.85)}
    .nav-links a:hover{color:var(--lime)}
    .hero-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:30px;align-items:center;margin-top:30px}
    .hero h1{font-size:60px;line-height:1.02;font-weight:800;letter-spacing:-1.5px}
    .hero h1 .accent{color:var(--lime)}
    .hero p.sub{margin:22px 0 30px;font-size:17px;color:rgba(255,255,255,.82);max-width:440px}
    .hero-cta{display:flex;gap:14px;flex-wrap:wrap}
    .hero-stats{display:flex;gap:34px;margin-top:38px}
    .hero-stats .n{font-size:26px;font-weight:800;color:var(--lime)}
    .hero-stats .l{font-size:13px;color:rgba(255,255,255,.7)}
    .hero-art{position:relative;height:420px}
    .hero-art .photo{position:absolute;border-radius:24px;background:#2c5a3e center/cover;box-shadow:0 30px 60px rgba(0,0,0,.3)}
    .hero-art .p1{width:300px;height:300px;right:0;top:10px;border:6px solid rgba(255,255,255,.12)}
    .hero-art .p2{width:170px;height:170px;left:0;bottom:0;border:6px solid rgba(255,255,255,.12)}
    .float-card{position:absolute;left:120px;top:0;background:#fff;color:var(--ink);border-radius:18px;padding:12px 14px;display:flex;gap:10px;align-items:center;box-shadow:0 18px 40px rgba(0,0,0,.22)}
    .float-card .ic{width:42px;height:42px;border-radius:12px;background:#eef7ef;display:grid;place-items:center;font-size:20px}
    .float-card .t{font-size:13px;font-weight:800}
    .float-card .s{font-size:11px;color:var(--muted)}

    /* ===== Marquee ===== */
    .marquee{background:var(--gold);color:#3a2600;overflow:hidden;white-space:nowrap;padding:14px 0;font-weight:800;letter-spacing:.5px}
    .marquee .track{display:inline-block;animation:scroll 22s linear infinite}
    .marquee span{margin:0 26px;font-size:14px;text-transform:uppercase}
    @keyframes scroll{from{transform:translateX(0)}to{transform:translateX(-50%)}}

    /* ===== Sections ===== */
    section.block{padding:88px 0}
    .center{text-align:center}
    h2.title{font-size:40px;font-weight:800;letter-spacing:-1px;margin:12px 0}
    .lead{color:var(--muted);max-width:560px;margin:0 auto;font-size:16px}

    .split{display:grid;grid-template-columns:1fr 1fr;gap:54px;align-items:center}
    .split .photo{height:380px;border-radius:28px;background:#dfece1 center/cover;box-shadow:0 24px 50px rgba(0,0,0,.12)}
    .split h2{font-size:36px;font-weight:800;letter-spacing:-1px;margin:14px 0 16px}
    .split p{color:var(--muted);margin-bottom:14px}
    .tick{display:flex;gap:12px;align-items:flex-start;margin-top:14px}
    .tick .b{flex:0 0 26px;height:26px;border-radius:8px;background:#eef7ef;color:var(--green);display:grid;place-items:center;font-weight:800}

    /* Why choose (gold) */
    .why{background:linear-gradient(180deg,#f7ad2f,#f59e16);border-radius:40px;color:#3a2600;margin:0 24px}
    .why .feature{background:#fff;border-radius:20px;padding:22px;box-shadow:0 14px 30px rgba(0,0,0,.08)}
    .why .feature .ic{width:48px;height:48px;border-radius:14px;background:#fff3da;display:grid;place-items:center;font-size:24px;margin-bottom:12px}
    .why .feature h4{font-size:18px;font-weight:800;margin-bottom:6px}
    .why .feature p{font-size:14px;color:var(--muted)}
    .why-grid{display:grid;grid-template-columns:1fr 1.1fr 1fr;gap:24px;align-items:center;margin-top:40px}
    .why-center{height:300px;border-radius:24px;background:#2c5a3e center/cover;box-shadow:0 24px 50px rgba(0,0,0,.2)}

    /* Dishes */
    .cards3{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-top:46px}
    .dish{background:#fff;border-radius:24px;overflow:hidden;box-shadow:0 14px 34px rgba(0,0,0,.07);transition:.2s}
    .dish:hover{transform:translateY(-6px)}
    .dish .img{height:190px;background:#dfece1 center/cover}
    .dish .body{padding:20px}
    .dish h4{font-size:18px;font-weight:800}
    .dish .desc{color:var(--muted);font-size:13px;margin:6px 0 14px}
    .dish .row{display:flex;align-items:center;justify-content:space-between}
    .dish .price{font-size:20px;font-weight:800;color:var(--green)}
    .dish .add{width:40px;height:40px;border-radius:12px;background:var(--green);color:#fff;display:grid;place-items:center;font-size:22px;border:none;cursor:pointer}

    /* Testimonials */
    .test{background:#fff;border-radius:24px;padding:26px;box-shadow:0 14px 30px rgba(0,0,0,.06)}
    .test .stars{color:var(--gold);font-weight:800}
    .test p{color:var(--ink);margin:12px 0 18px;font-size:15px}
    .test .who{display:flex;align-items:center;gap:12px}
    .test .who img{width:46px;height:46px;border-radius:50%;background:#eee}
    .test .who .nm{font-weight:800;font-size:15px}
    .test .who .ro{font-size:12px;color:var(--muted)}

    /* Tips (green) */
    .tips{background:var(--green);color:#fff;border-radius:40px;margin:0 24px;padding:80px 0}
    .tip{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:20px;overflow:hidden}
    .tip .img{height:150px;background:#2c5a3e center/cover}
    .tip .body{padding:18px}
    .tip h4{font-size:16px;font-weight:800;margin-bottom:6px}
    .tip p{font-size:13px;color:rgba(255,255,255,.7)}

    /* CTA */
    .cta{background:linear-gradient(180deg,#f7ad2f,#ef9410);border-radius:40px;margin:0 24px;text-align:center;padding:70px 24px 0;color:#3a2600;overflow:hidden;position:relative}
    .cta h2{font-size:46px;font-weight:800;letter-spacing:-1px}
    .cta p{max-width:520px;margin:14px auto 26px;color:#5a3f12}
    .bigword{font-size:clamp(60px,18vw,210px);font-weight:800;letter-spacing:-4px;color:#2f6a45;line-height:.9;margin-top:30px;opacity:.92}

    /* Footer */
    footer{background:var(--green-deep);color:rgba(255,255,255,.75);padding:60px 0 30px}
    .foot-grid{display:grid;grid-template-columns:1.4fr 1fr 1fr 1fr;gap:30px}
    footer h5{color:#fff;font-weight:800;margin-bottom:14px;font-size:15px}
    footer a{display:block;color:rgba(255,255,255,.7);font-size:14px;margin-bottom:8px}
    footer a:hover{color:var(--lime)}
    .foot-bottom{border-top:1px solid rgba(255,255,255,.12);margin-top:40px;padding-top:20px;display:flex;justify-content:space-between;font-size:13px;flex-wrap:wrap;gap:10px}

    @media (max-width:880px){
      .hero h1{font-size:42px}
      .hero-grid,.split,.why-grid,.cards3,.foot-grid{grid-template-columns:1fr}
      .hero-art{height:300px;margin-top:20px}
      .nav-links{display:none}
      h2.title{font-size:30px}
      .cards3{gap:18px}
    }
  </style>
</head>
<body>

  <!-- HERO -->
  <header class="hero">
    <div class="wrap">
      <nav>
        <div class="logo"><span class="mark">🌿</span> Hyperlocal</div>
        <div class="nav-links">
          <a href="#about">About</a>
          <a href="#dishes">Menu</a>
          <a href="#reviews">Reviews</a>
          <a href="#contact">Contact</a>
        </div>
        <a class="btn btn-gold" href="#dishes">Order Now</a>
      </nav>

      <div class="hero-grid">
        <div>
          <span class="pill">🍃 Hyperlocal food, delivered fast</span>
          <h1 style="margin-top:18px">Fuel Your Day with<br><span class="accent">Flavor That Matters</span></h1>
          <p class="sub">Fresh meals from your neighbourhood kitchens — made daily, delivered hot, and crafted for the way you live.</p>
          <div class="hero-cta">
            <a class="btn btn-gold" href="#dishes">Order Now →</a>
            <a class="btn btn-ghost" href="#dishes">Explore Menu</a>
          </div>
          <div class="hero-stats">
            <div><div class="n">8+</div><div class="l">Local kitchens</div></div>
            <div><div class="n">20 min</div><div class="l">Avg delivery</div></div>
            <div><div class="n">4.8★</div><div class="l">Customer rating</div></div>
          </div>
        </div>
        <div class="hero-art">
          <div class="photo p1" style="background-image:url('https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=700&q=80&auto=format&fit=crop')"></div>
          <div class="photo p2" style="background-image:url('https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=500&q=80&auto=format&fit=crop')"></div>
          <div class="float-card">
            <div class="ic">🛵</div>
            <div><div class="t">Free delivery</div><div class="s">on your first order</div></div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- MARQUEE -->
  <div class="marquee">
    <div class="track">
      <span>★ FAST DELIVERY</span><span>★ MADE DAILY</span><span>★ LOVED BY CUSTOMERS</span><span>★ NO ARTIFICIAL ADDITIVES</span><span>★ FRESH INGREDIENTS</span>
      <span>★ FAST DELIVERY</span><span>★ MADE DAILY</span><span>★ LOVED BY CUSTOMERS</span><span>★ NO ARTIFICIAL ADDITIVES</span><span>★ FRESH INGREDIENTS</span>
    </div>
  </div>

  <!-- ABOUT -->
  <section class="block" id="about">
    <div class="wrap split">
      <div class="photo" style="background-image:url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&q=80&auto=format&fit=crop')"></div>
      <div>
        <span class="eyebrow">● Our promise</span>
        <h2>We believe food is more than just food</h2>
        <p>Every meal on Hyperlocal is prepared by real kitchens in your area using fresh, quality ingredients — never frozen, never mass-produced.</p>
        <div class="tick"><div class="b">✓</div><div><strong>Fresh &amp; locally sourced</strong><br><span style="color:var(--muted);font-size:14px">Ingredients delivered to kitchens daily.</span></div></div>
        <div class="tick"><div class="b">✓</div><div><strong>Crafted by real chefs</strong><br><span style="color:var(--muted);font-size:14px">Recipes from neighbourhood favourites.</span></div></div>
        <div class="tick"><div class="b">✓</div><div><strong>Delivered fast &amp; hot</strong><br><span style="color:var(--muted);font-size:14px">Riders nearby get it to you in minutes.</span></div></div>
      </div>
    </div>
  </section>

  <!-- WHY -->
  <section class="why block">
    <div class="wrap center">
      <span class="eyebrow" style="color:#7a4d00">● Why Hyperlocal</span>
      <h2 class="title">Why Choose Us</h2>
      <p class="lead" style="color:#6b4a12">Good food, done right — and delivered the way it should be.</p>
      <div class="why-grid">
        <div>
          <div class="feature"><div class="ic">🥗</div><h4>Fresh &amp; Organic</h4><p>Wholesome ingredients with no artificial additives.</p></div>
          <div class="feature" style="margin-top:24px"><div class="ic">👩‍🍳</div><h4>Crafted by Pros</h4><p>Prepared by trusted local kitchens and chefs.</p></div>
        </div>
        <div class="why-center" style="background-image:url('https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=700&q=80&auto=format&fit=crop')"></div>
        <div>
          <div class="feature"><div class="ic">⚡</div><h4>Lightning Fast</h4><p>Average delivery in around 20 minutes.</p></div>
          <div class="feature" style="margin-top:24px"><div class="ic">💚</div><h4>Loved Locally</h4><p>Rated 4.8★ by customers across the city.</p></div>
        </div>
      </div>
    </div>
  </section>

  <!-- DISHES -->
  <section class="block center" id="dishes">
    <div class="wrap">
      <span class="eyebrow">● On the menu</span>
      <h2 class="title">Our Signature Dishes</h2>
      <p class="lead">A taste of what your neighbourhood kitchens are cooking right now.</p>
      <div class="cards3" style="text-align:left">
        <div class="dish">
          <div class="img" style="background-image:url('https://images.unsplash.com/photo-1532550907401-a500c9a57435?w=600&q=80&auto=format&fit=crop')"></div>
          <div class="body"><h4>Grilled Chicken Bowl</h4><p class="desc">Char-grilled chicken, rice &amp; seasonal greens.</p><div class="row"><span class="price">$10.50</span><button class="add">+</button></div></div>
        </div>
        <div class="dish">
          <div class="img" style="background-image:url('https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&q=80&auto=format&fit=crop')"></div>
          <div class="body"><h4>Avocado Power Salad</h4><p class="desc">Avocado, greens, seeds &amp; citrus dressing.</p><div class="row"><span class="price">$8.00</span><button class="add">+</button></div></div>
        </div>
        <div class="dish">
          <div class="img" style="background-image:url('https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&q=80&auto=format&fit=crop')"></div>
          <div class="body"><h4>Smash Burger Combo</h4><p class="desc">Double smashed patties, cheddar &amp; house sauce.</p><div class="row"><span class="price">$9.50</span><button class="add">+</button></div></div>
        </div>
      </div>
    </div>
  </section>

  <!-- REVIEWS -->
  <section class="block center" id="reviews" style="background:#fff;border-radius:40px;margin:0 24px">
    <div class="wrap">
      <span class="eyebrow">● Happy customers</span>
      <h2 class="title">What Our Customers Say</h2>
      <div class="cards3" style="text-align:left">
        <div class="test"><div class="stars">★★★★★ 4.8</div><p>“The smash burger arrived hot and the delivery was lightning fast. Easily my go-to now.”</p><div class="who"><img src="https://i.pravatar.cc/120?img=12" alt=""><div><div class="nm">Daniel R.</div><div class="ro">Customer</div></div></div></div>
        <div class="test"><div class="stars">★★★★★ 4.8</div><p>“Authentic jollof just like home, generous portions, and always fresh. Love it.”</p><div class="who"><img src="https://i.pravatar.cc/120?img=32" alt=""><div><div class="nm">Sarah L.</div><div class="ro">Customer</div></div></div></div>
        <div class="test"><div class="stars">★★★★★ 4.9</div><p>“Healthy bowls that actually taste amazing. The app makes reordering effortless.”</p><div class="who"><img src="https://i.pravatar.cc/120?img=45" alt=""><div><div class="nm">Amanda K.</div><div class="ro">Customer</div></div></div></div>
      </div>
    </div>
  </section>

  <!-- TIPS -->
  <section class="tips">
    <div class="wrap">
      <div class="center"><span class="pill">● Food tips &amp; inspiration</span><h2 class="title" style="color:#fff">Eat Well, Live Well</h2></div>
      <div class="cards3" style="margin-top:40px">
        <div class="tip"><div class="img" style="background-image:url('https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=600&q=80&auto=format&fit=crop')"></div><div class="body"><h4>5 ways to build a balanced bowl</h4><p>Simple swaps for more energy through the day.</p></div></div>
        <div class="tip"><div class="img" style="background-image:url('https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=600&q=80&auto=format&fit=crop')"></div><div class="body"><h4>Why local kitchens taste better</h4><p>Fresher ingredients, shorter trips, more flavour.</p></div></div>
        <div class="tip"><div class="img" style="background-image:url('https://images.unsplash.com/photo-1466637574441-749b8f19452f?w=600&q=80&auto=format&fit=crop')"></div><div class="body"><h4>Meal prep for busy weeks</h4><p>Order smart and keep your week deliciously simple.</p></div></div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="block" style="padding-bottom:40px">
    <div class="cta">
      <span class="eyebrow" style="color:#7a4d00">● Ready when you are</span>
      <h2>Satisfy Your Cravings, Fast &amp; Fresh</h2>
      <p>Download the app or order online — your next great meal is minutes away.</p>
      <a class="btn btn-dark" href="#dishes">Start your order →</a>
      <div class="bigword">HYPERLOCAL</div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer id="contact">
    <div class="wrap">
      <div class="foot-grid">
        <div>
          <div class="logo" style="margin-bottom:14px"><span class="mark">🌿</span> Hyperlocal</div>
          <p style="font-size:14px;max-width:280px">Fresh food crafted for modern lifestyles, delivered from your neighbourhood kitchens.</p>
        </div>
        <div><h5>Company</h5><a href="#about">About</a><a href="#dishes">Menu</a><a href="#reviews">Reviews</a></div>
        <div><h5>Support</h5><a href="mailto:support@hyperlocal.test">Help &amp; Support</a><a href="#contact">Contact</a><a href="/api/categories">API status</a></div>
        <div><h5>Get the app</h5><a href="#">iOS — Expo Go</a><a href="#">Android — Expo Go</a></div>
      </div>
      <div class="foot-bottom">
        <span>© 2026 Hyperlocal. All rights reserved.</span>
        <span>Made fresh, delivered fast.</span>
      </div>
    </div>
  </footer>

</body>
</html>
@endverbatim
