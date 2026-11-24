@php
    $total = \App\Models\ParkingSlot::count();
    $aktif = \App\Models\Booking::where('status', 'aktif')->count();
    $bebas = max(0, $total - $aktif);
    $hari  = \App\Models\Booking::whereDate('waktu_masuk', today())->count();
    $pct   = $total > 0 ? min(100, (int) round($aktif / $total * 100)) : 0;
    $off   = round(264 * (1 - $pct / 100), 1);
    $cta   = auth()->check() ? url('/dashboard') : (Route::has('login') ? route('login') : url('/login'));
    $ctaTx = auth()->check() ? 'Buka Dashboard' : 'Masuk Sistem';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kabasa Parkir</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;800&display=swap" rel="stylesheet">
<style>
:root{--b1:#38bdf8;--b2:#2563eb;--bg:#05070f;--tx:#e6edf7;--mu:#8da2c0}
*{box-sizing:border-box;margin:0}
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--tx);font-family:'Sora',system-ui,sans-serif;overflow-x:hidden}
a{color:inherit;text-decoration:none}
#bar{position:fixed;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--b1),var(--b2));transform-origin:0 50%;transform:scaleX(0);z-index:60}
nav{position:fixed;top:18px;left:24px;right:24px;display:flex;justify-content:space-between;align-items:center;z-index:50;transition:top .4s}
nav.sm{top:8px}
.pill{display:flex;gap:4px;padding:5px;border:1px solid rgba(125,170,255,.35);border-radius:99px;background:rgba(8,14,30,.65);backdrop-filter:blur(10px);font-size:13px;font-weight:600}
.pill a{padding:8px 16px;border-radius:99px;transition:.3s}
.pill a:hover{background:linear-gradient(135deg,#7dd3fc,#3b82f6);color:#04101f}
.cta{padding:12px 24px;border-radius:99px;font-weight:700;font-size:13px;color:#04101f;background:linear-gradient(135deg,#bae6fd,#38bdf8);box-shadow:0 0 28px rgba(56,189,248,.45);transition:.3s;display:inline-block}
.cta:hover{transform:translateY(-2px);box-shadow:0 0 44px rgba(56,189,248,.75)}
/* hero */
.hero{position:relative;min-height:100vh;display:grid;place-items:center;overflow:hidden;padding:120px 24px 60px}
.fogw{position:absolute;inset:0;transform:translateY(calc(var(--sy,0)*.25px))}
.fog{position:absolute;inset:-10%;background:radial-gradient(40% 35% at 22% 55%,rgba(56,189,248,.38),transparent 70%),radial-gradient(45% 40% at 80% 45%,rgba(37,99,235,.45),transparent 70%);filter:blur(30px);animation:drift 12s ease-in-out infinite alternate}
@keyframes drift{to{transform:translate(3%,-3%) scale(1.1)}}
.ttl{position:absolute;top:14vh;left:0;right:0;text-align:center;font-weight:800;line-height:.95;transform:translateY(calc(var(--sy,0)*.3px));opacity:calc(1 - var(--sy,0)/650)}
.ttl b{display:block;font-size:clamp(56px,11vw,150px);background:linear-gradient(180deg,#fff,#93c5fd);-webkit-background-clip:text;background-clip:text;color:transparent;filter:drop-shadow(0 0 28px rgba(56,189,248,.5))}
.ttl span{display:block;font-size:clamp(28px,6vw,84px);letter-spacing:.1em;color:#e6f4ff}
.hcar{width:min(900px,92vw);margin-top:24vh;position:relative;animation:float 5s ease-in-out infinite;filter:drop-shadow(0 0 40px rgba(56,189,248,.35))}
@keyframes float{50%{transform:translateY(-12px)}}
.tag{position:absolute;left:28px;top:36vh;max-width:210px;font-size:14px;line-height:1.7;color:#cfe1f7}
.tag::first-letter{font-size:32px;font-weight:600}
.sc{position:absolute;right:28px;bottom:34px;font-size:12px;color:var(--mu);text-align:center}
.sc i{display:block;font-style:normal;font-size:20px;animation:bob 1.6s infinite}
@keyframes bob{50%{transform:translateY(6px)}}
.orb{position:absolute;left:50%;bottom:15vh;margin-left:-52px;width:104px;height:104px;border-radius:50%;display:grid;place-items:center;text-align:center;font-size:13px;font-weight:700;line-height:1.3;color:#04101f;background:radial-gradient(circle,#e0f2fe,#7dd3fc);animation:orb 2.4s infinite}
@keyframes orb{0%,100%{box-shadow:0 0 0 8px rgba(125,211,252,.15),0 0 40px rgba(56,189,248,.5)}50%{box-shadow:0 0 0 18px rgba(125,211,252,.06),0 0 70px rgba(56,189,248,.8)}}
.wh{transform-box:fill-box;transform-origin:center;animation:spin 1.2s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
/* sections */
.sec{position:relative;padding:110px 7vw}
.phil{display:grid;grid-template-columns:1fr 1.4fr 1fr;gap:40px;align-items:center;border-top:1px solid #151a2b}
.phil h2,.big h2{font-weight:300;font-size:clamp(28px,4vw,52px);line-height:1.15}
.phil p{font-size:20px;line-height:1.8;color:#c9d8ee}
.phil p::first-letter{font-size:44px;font-weight:600;color:var(--b1)}
.big{display:grid;grid-template-columns:1.1fr 1fr;gap:48px;align-items:center}
.big .card{margin-top:28px;padding:26px;background:#10131d;border-left:3px solid var(--b1);font-size:14px;line-height:1.8;color:#b9c8de}
.num{margin-top:22px;font-size:14px;color:var(--b1);letter-spacing:.2em}
.panel{padding:26px;border-radius:24px;border:1px solid #1e2a44;background:linear-gradient(160deg,rgba(56,189,248,.1),rgba(37,99,235,.05));box-shadow:0 0 60px rgba(37,99,235,.18)}
.gw{display:flex;align-items:center;gap:22px}
.gauge{position:relative;width:130px;height:130px;flex:none}
.gauge svg{width:100%;height:100%;transform:rotate(-90deg)}
.ring{stroke-dasharray:264;stroke-dashoffset:264;transition:stroke-dashoffset 2s ease-out .3s}
.in .ring{stroke-dashoffset:var(--off)}
.gauge div{position:absolute;inset:0;display:grid;place-content:center;text-align:center;font-weight:800;font-size:26px}
.gauge small{font-size:10px;font-weight:400;color:var(--mu)}
.st{display:grid;grid-template-columns:1fr 1fr;gap:10px;flex:1}
.st div{padding:12px;border-radius:14px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08)}
.st b{display:block;font-size:26px}.st small{font-size:11px;color:var(--mu)}
.live{margin-top:18px;font-size:12px;color:#34d399;display:flex;align-items:center;gap:8px}
.live i{width:9px;height:9px;border-radius:50%;background:#34d399;animation:orb 1.6s infinite}
/* driving car */
.drive{position:relative;height:75vh;overflow:hidden;border-top:1px solid #151a2b;background:radial-gradient(60% 50% at 50% 100%,rgba(37,99,235,.28),transparent 70%)}
.drive h2{position:absolute;top:14%;left:7vw;right:7vw;font-weight:300;font-size:clamp(28px,5vw,64px);line-height:1.1}
.drive h2 em{font-style:normal;color:var(--b1);font-weight:800}
.road{position:absolute;bottom:20%;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,var(--b1),transparent);box-shadow:0 0 28px var(--b1)}
#car2{position:absolute;bottom:calc(20% + 2px);left:0;width:320px;will-change:transform;filter:drop-shadow(0 0 24px rgba(56,189,248,.6))}
/* roles */
.hd{font-weight:300;font-size:clamp(26px,3.6vw,46px);margin-bottom:44px;max-width:640px;line-height:1.2}
.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.rc{padding:30px;border-radius:20px;border:1px solid #1e2a44;background:rgba(255,255,255,.03);transition:.4s}
.rc:hover{transform:translateY(-8px);border-color:var(--b1);box-shadow:0 0 40px rgba(56,189,248,.25)}
.rc h3{font-size:22px;margin-bottom:10px;color:var(--b1)}.rc p{font-size:14px;line-height:1.8;color:#b9c8de}
/* steps */
.steps{position:relative;padding-left:56px;max-width:720px}
.rail{position:absolute;left:19px;top:6px;bottom:6px;width:2px;background:#1a2238}
#fill{position:absolute;inset:0;background:linear-gradient(var(--b1),var(--b2));transform-origin:50% 0;transform:scaleY(0);box-shadow:0 0 14px var(--b1)}
.step{position:relative;margin-bottom:46px}
.step::before{content:attr(data-n);position:absolute;left:-56px;top:0;width:40px;height:40px;border-radius:50%;display:grid;place-items:center;font-weight:800;background:#0b1226;border:2px solid var(--b1);box-shadow:0 0 18px rgba(56,189,248,.5)}
.step h3{font-size:20px;margin-bottom:6px}.step p{font-size:14px;line-height:1.8;color:#b9c8de}
/* end */
.end{text-align:center;padding:130px 7vw;background:radial-gradient(60% 70% at 50% 0%,rgba(37,99,235,.35),transparent 70%)}
.end h2{font-weight:800;font-size:clamp(30px,5vw,64px);margin-bottom:30px}
footer{display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;padding:28px 7vw;border-top:1px solid #151a2b;font-size:13px;color:var(--mu)}
/* reveal */
.rv{opacity:0;transform:translateY(34px);transition:opacity .9s cubic-bezier(.2,.7,.2,1) var(--d,0s),transform .9s cubic-bezier(.2,.7,.2,1) var(--d,0s)}
.rv.in{opacity:1;transform:none}
@media(max-width:860px){.phil,.big,.cards{grid-template-columns:1fr}.tag,.pill{display:none}#car2{width:200px}.gw{flex-direction:column}}
@media(prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}.rv{opacity:1;transform:none}}
</style>
</head>
<body>
<svg width="0" height="0" style="position:absolute"><defs>
  <symbol id="car" viewBox="0 0 400 120">
    <ellipse cx="208" cy="98" rx="190" ry="7" fill="url(#ug)"/>
    <path d="M388 70 L400 58 L400 86Z" fill="url(#beam)"/>
    <path d="M24 56 L60 50 L62 54 L30 61Z" fill="#0b1630" stroke="#0753f7" stroke-width="1"/>
    <path d="M44 58 L46 67" stroke="#0a5bf0" stroke-width="1.5"/>
    <path d="M32 90 L28 74 Q30 63 50 59 L98 53 Q132 29 192 26 Q238 25 264 41 L332 57 Q374 62 386 75 L389 90Z" fill="url(#cb)" stroke="#38bdf8" stroke-width="1.5" stroke-linejoin="round"/>
    <path d="M116 52 Q140 34 192 31 Q230 30 250 43 L288 54Z" fill="url(#cg)" stroke="#1142e2" stroke-width=".9"/>
    <path d="M190 31 L186 53M52 67 Q200 57 372 71M64 81 L250 81" fill="none" stroke="#085bf5" stroke-opacity=".45" stroke-width="1"/>
    <path d="M258 46 L271 44 L272 48 L260 49Z" fill="#0b1630" stroke="#0159fc" stroke-width=".8"/>
    <path d="M354 66 L387 72" stroke="#e0f2fe" stroke-width="3.5" stroke-linecap="round"/>
    <path d="M27 69 L38 67" stroke="#0c40eb" stroke-width="3.5" stroke-linecap="round"/>
    <g transform="translate(104 84)"><circle r="27" fill="#04060d"/><circle r="22" fill="#070b14" stroke="#1e293b" stroke-width="2"/><circle r="15" fill="url(#rim)" stroke="#38bdf8" stroke-width="1.6"/><g class="wh"><path d="M0-14V14M-12.1-7L12.1 7M-12.1 7L12.1-7" stroke="#7dd3fc" stroke-width="2"/></g><circle r="3.5" fill="#38bdf8"/></g>
    <g transform="translate(300 84)"><circle r="27" fill="#04060d"/><circle r="22" fill="#070b14" stroke="#1e293b" stroke-width="2"/><circle r="15" fill="url(#rim)" stroke="#38bdf8" stroke-width="1.6"/><g class="wh"><path d="M0-14V14M-12.1-7L12.1 7M-12.1 7L12.1-7" stroke="#7dd3fc" stroke-width="2"/></g><circle r="3.5" fill="#38bdf8"/></g>
  </symbol>
  <linearGradient id="cb" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#4f86f0"/><stop offset=".3" stop-color="#1d3f95"/><stop offset="1" stop-color="#070d1f"/></linearGradient>
  <linearGradient id="cg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#bfe3ff" stop-opacity=".55"/><stop offset=".5" stop-color="#0f2150"/><stop offset="1" stop-color="#050a18"/></linearGradient>
  <radialGradient id="rim"><stop offset="0" stop-color="#1e3a8a"/><stop offset="1" stop-color="#0b1226"/></radialGradient>
  <linearGradient id="beam" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#e0f2fe" stop-opacity=".8"/><stop offset="1" stop-color="#e0f2fe" stop-opacity="0"/></linearGradient>
  <radialGradient id="ug"><stop offset="0" stop-color="#38bdf8" stop-opacity=".6"/><stop offset="1" stop-color="#38bdf8" stop-opacity="0"/></radialGradient>
  <linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#38bdf8"/><stop offset="1" stop-color="#2563eb"/></linearGradient>
</defs></svg>

<div id="bar"></div>
<nav id="nav">
  <div class="pill"><a href="#top">Beranda</a><a href="#filosofi">Filosofi</a><a href="#peran">Peran</a><a href="#alur">Alur</a></div>
  <a class="cta" href="{{ $cta }}">{{ $ctaTx }}</a>
</nav>

<header class="hero" id="top">
  <div class="fogw"><div class="fog"></div></div>
  <div class="ttl"><b>KABASA</b><span>PARKIR</span></div>
  <svg class="hcar" viewBox="0 0 300 90"><use href="#car"/></svg>
  <a class="orb" href="#filosofi">Lihat<br>Fitur<br>↓</a>
  <div class="sc"><i>↓</i>Gulir untuk menjelajah</div>
</header>

<section class="sec phil" id="filosofi">
  <h2 class="rv">Filosofi Kami?</h2>
  <p class="rv" style="--d:.15s">Kami menata parkir dengan mencatat setiap kendaraan, menghitung tarif otomatis, dan mencetak struk tanpa repot.</p>
  <div class="rv" style="--d:.3s"><h2 style="font-size:clamp(22px,2.4vw,30px)">Jadi bagian dari parkir yang tertata.</h2><br><a class="cta" href="{{ $cta }}">{{ $ctaTx }}</a></div>
</section>

<section class="sec big">
  <div>
    <h2 class="rv">BUKAN SEKADAR CATATAN PARKIR.</h2>
    <div class="card rv" style="--d:.15s">Parkir Kabasa adalah satu aplikasi untuk petugas, admin, dan owner. Kendaraan masuk dan keluar tercatat, tarif mengikuti pengaturan admin, dan pendapatan terpantau langsung.</div>
    <div class="num rv" style="--d:.25s">01</div>
  </div>
  <div class="panel rv" style="--d:.2s">
    <div class="gw">
      <div class="gauge">
        <svg viewBox="0 0 100 100"><circle cx="50" cy="50" r="42" fill="none" stroke="rgba(255,255,255,.08)" stroke-width="9"/><circle class="ring" cx="50" cy="50" r="42" fill="none" stroke="url(#g)" stroke-width="9" stroke-linecap="round" style="--off:{{ $off }}"/></svg>
        <div>{{ $pct }}%<small>terisi</small></div>
      </div>
      <div class="st">
        <div><b data-to="{{ $total }}">0</b><small>Total slot</small></div>
        <div><b data-to="{{ $bebas }}" style="color:#34d399">0</b><small>Tersedia</small></div>
        <div><b data-to="{{ $aktif }}" style="color:#fbbf24">0</b><small>Sedang parkir</small></div>
        <div><b data-to="{{ $hari }}" style="color:#60a5fa">0</b><small>Masuk hari ini</small></div>
      </div>
    </div>
    <div class="live"><i></i>Data langsung dari sistem</div>
  </div>
</section>

<section class="drive" id="drive">
  <h2 class="rv">Kami percaya parkir <em>tak perlu ribet.</em></h2>
  <div class="road"></div>
  <svg id="car2" viewBox="0 0 300 90"><use href="#car"/></svg>
</section>

<section class="sec" id="peran">
  <h2 class="hd rv">Satu aplikasi, tiga peran.</h2>
  <div class="cards">
    <div class="rc rv"><h3>Petugas</h3><p>Mencatat kendaraan masuk dan keluar langsung di lapangan, lalu mencetak struk.</p></div>
    <div class="rc rv" style="--d:.12s"><h3>Admin</h3><p>Mengatur slot, area, tarif, dan akun, serta memantau semua transaksi.</p></div>
    <div class="rc rv" style="--d:.24s"><h3>Owner</h3><p>Melihat pendapatan dan aktivitas tanpa perlu mengurus data harian.</p></div>
  </div>
</section>

<section class="sec" id="alur">
  <h2 class="hd rv">Dari masuk sampai struk, empat langkah.</h2>
  <div class="steps" id="steps">
    <div class="rail"><div id="fill"></div></div>
    <div class="step rv" data-n="1"><h3>Catat masuk</h3><p>Petugas mengetik plat. Kendaraan baru bisa langsung ditambahkan di tempat.</p></div>
    <div class="step rv" data-n="2"><h3>Tarif otomatis</h3><p>Tarif per jam mengikuti pengaturan admin sesuai jenis kendaraan.</p></div>
    <div class="step rv" data-n="3"><h3>Keluar dan bayar</h3><p>Durasi dihitung otomatis dan pembayaran tercatat lunas.</p></div>
    <div class="step rv" data-n="4"><h3>Cetak struk</h3><p>Struk siap dicetak kapan saja dari daftar transaksi hari ini.</p></div>
  </div>
</section>

<section class="end">
  <h2 class="rv">Kelola parkir lebih rapi, mulai hari ini.</h2>
  <a class="cta rv" style="--d:.15s" href="{{ $cta }}">{{ $ctaTx }}</a>
</section>

<footer><span>Parkir Kabasa</span><span>&copy; {{ date('Y') }} Kabasa. Semua hak dilindungi.</span></footer>

<script>
(function(){
  var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){e.target.classList.add('in');io.unobserve(e.target)}})},{threshold:.2});
  document.querySelectorAll('.rv').forEach(function(el){io.observe(el)});

  var co=new IntersectionObserver(function(es){es.forEach(function(e){
    if(!e.isIntersecting)return;var el=e.target,to=+el.dataset.to,n=0,s=Math.max(1,Math.ceil(to/40));
    var t=setInterval(function(){n=Math.min(to,n+s);el.textContent=n;if(n>=to)clearInterval(t)},30);co.unobserve(el)})},{threshold:.6});
  document.querySelectorAll('[data-to]').forEach(function(el){co.observe(el)});

  var root=document.documentElement,bar=document.getElementById('bar'),nav=document.getElementById('nav'),
      drive=document.getElementById('drive'),car=document.getElementById('car2'),
      steps=document.getElementById('steps'),fill=document.getElementById('fill'),tick=false;
  function clamp(v){return Math.min(1,Math.max(0,v))}
  function onScroll(){
    var y=window.scrollY,h=root.scrollHeight-innerHeight;
    root.style.setProperty('--sy',y);
    bar.style.transform='scaleX('+clamp(y/h)+')';
    nav.classList.toggle('sm',y>40);
    var r=drive.getBoundingClientRect();
    car.style.transform='translateX('+(clamp((innerHeight-r.top)/(innerHeight+r.height))*(drive.clientWidth+400)-360)+'px)';
    r=steps.getBoundingClientRect();
    fill.style.transform='scaleY('+clamp((innerHeight*.7-r.top)/r.height)+')';
    tick=false;
  }
  addEventListener('scroll',function(){if(!tick){tick=true;requestAnimationFrame(onScroll)}},{passive:true});
  onScroll();
})();
</script>
</body>
</html>