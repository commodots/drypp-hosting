<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Drypp — Hosting, Marketing & Growth')</title>
<meta name="description" content="Domains, reliable web hosting, email marketing and prospecting tools from Drypp.">
<link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--navy:#0b1530;--blue:#4169f5;--cyan:#4bd5e8;--ink:#14203b;--muted:#65708a;--line:#e7ebf4;--pale:#f5f7ff}*{box-sizing:border-box}body{margin:0;font-family:'DM Sans',sans-serif;color:var(--ink);background:#fff}h1,h2,h3{font-family:Manrope,sans-serif;letter-spacing:-.04em}a{text-decoration:none;color:inherit}.wrap{width:min(1160px,calc(100% - 40px));margin:auto}.nav{display:flex;align-items:center;justify-content:space-between;height:82px}.brand{display:flex;align-items:center;gap:10px;font:800 24px Manrope;color:var(--navy)}.drop{width:34px;height:34px;border-radius:11px;background:linear-gradient(135deg,#4169f5,#42d0df);display:grid;place-items:center;color:white;font-size:20px}.navlinks{display:flex;align-items:center;gap:28px;color:#46516b;font-size:14px;font-weight:600}.actions{display:flex;gap:10px;align-items:center}.btn{display:inline-flex;align-items:center;justify-content:center;padding:12px 18px;border-radius:12px;font-weight:700;font-size:14px;border:1px solid transparent;cursor:pointer}.btn-primary{background:var(--blue);color:#fff;box-shadow:0 8px 20px #4169f52b}.btn-light{border-color:var(--line);background:#fff;color:var(--ink)}.hero{background:radial-gradient(circle at 85% 12%,#dce9ff 0,transparent 30%),linear-gradient(130deg,#f7f9ff,#edf4ff);padding:72px 0 76px;overflow:hidden}.hero-grid{display:grid;grid-template-columns:1.06fr .94fr;gap:55px;align-items:center}.eyebrow{display:inline-flex;gap:8px;align-items:center;padding:7px 11px;background:#e7edff;border-radius:99px;color:#3155d8;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.08em}.hero h1{font-size:clamp(42px,5vw,64px);line-height:1.06;margin:20px 0}.hero h1 span{color:var(--blue)}.hero p{color:var(--muted);font-size:18px;line-height:1.75;max-width:570px}.hero-actions{display:flex;gap:12px;margin-top:28px;flex-wrap:wrap}.proof{display:flex;gap:18px;margin-top:26px;color:#65708a;font-size:13px;flex-wrap:wrap}.proof span:before{content:'✓';color:#1d9c78;font-weight:900;margin-right:7px}.dash-art{background:var(--navy);border-radius:25px;padding:20px;color:white;box-shadow:0 35px 80px #13244b33;transform:rotate(1deg)}.dash-top{display:flex;justify-content:space-between;align-items:center;padding:4px 2px 20px;font-size:12px;color:#c2cbe1}.live{background:#143e3e;color:#69e5be;padding:6px 9px;border-radius:8px}.statgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}.stat{background:#172548;border:1px solid #29375a;border-radius:14px;padding:14px}.stat small{display:block;color:#a6b5d6;font-size:11px}.stat strong{display:block;font-size:22px;margin-top:7px}.chart{margin-top:12px;background:#121f3f;border:1px solid #29375a;border-radius:14px;padding:18px;height:160px;display:flex;align-items:end;gap:8px}.bar{flex:1;border-radius:7px 7px 2px 2px;background:linear-gradient(180deg,#55d8e7,#4169f5)}.section{padding:76px 0}.section-head{text-align:center;max-width:670px;margin:0 auto 35px}.section-head h2{font-size:38px;margin:0 0 13px}.section-head p{color:var(--muted);line-height:1.7}.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.card{border:1px solid var(--line);border-radius:20px;padding:25px;background:white;box-shadow:0 12px 30px #15285808}.iconbox{height:48px;width:48px;display:grid;place-items:center;border-radius:15px;background:#edf1ff;color:var(--blue);font-size:23px}.card h3{font-size:20px;margin:19px 0 8px}.card p{color:var(--muted);line-height:1.65;font-size:14px}.card a{display:inline-flex;margin-top:12px;color:var(--blue);font-weight:800;font-size:14px}.price{font:800 27px Manrope;margin:15px 0}.price small{font:500 12px 'DM Sans';color:var(--muted)}.features{list-style:none;padding:0;margin:20px 0;color:#53607b;font-size:14px;line-height:2}.features li:before{content:'✓';color:#168b70;font-weight:900;margin-right:9px}.pricing{background:#f8faff}.pricecard{position:relative}.pricecard.featured{border:2px solid var(--blue);box-shadow:0 20px 45px #4169f51c}.pill{display:inline-block;background:#e8edff;color:#3455d9;border-radius:50px;padding:6px 10px;font-size:11px;font-weight:800}.promo{background:linear-gradient(120deg,#101e43,#1e3f87);border-radius:25px;padding:40px;color:white;display:flex;align-items:center;justify-content:space-between;gap:20px}.promo h2{font-size:32px;margin:0 0 10px}.promo p{color:#d4def4;line-height:1.7;max-width:650px}.footer{background:#0b1530;color:#b9c5de;padding:36px 0;margin-top:60px;font-size:13px}.footer-row{display:flex;justify-content:space-between;gap:20px;flex-wrap:wrap}.notice{padding:12px 14px;background:#effaf5;border:1px solid #c9efdf;color:#176b4b;border-radius:10px;margin-bottom:20px}.form-control{width:100%;padding:12px 13px;border:1px solid #dce2ef;border-radius:10px;background:white;font:inherit}.form-label{display:block;font-size:13px;font-weight:700;margin:0 0 7px}.field{margin-bottom:14px}.error{color:#b42318;font-size:12px;margin-top:4px}.table-wrap{overflow-x:auto}table{width:100%;border-collapse:collapse;font-size:13px}th,td{text-align:left;padding:13px 12px;border-bottom:1px solid var(--line)}th{color:#64708a;background:#f8faff;font-size:11px;text-transform:uppercase;letter-spacing:.06em}.status{display:inline-block;padding:5px 8px;border-radius:8px;background:#eef2ff;color:#4359b8;font-size:11px;font-weight:800}.panel{border:1px solid var(--line);border-radius:16px;background:#fff;padding:20px;margin-bottom:22px}.grid2{display:grid;grid-template-columns:1fr 1fr;gap:20px}.dashboard-shell{background:#f5f7fb;min-height:100vh}.appbar{background:#fff;border-bottom:1px solid var(--line)}.appnav{height:72px;display:flex;align-items:center;justify-content:space-between}.appnav .navlinks{gap:16px}.content{padding:35px 0}.content h1{font-size:34px;margin:0 0 8px}.muted{color:var(--muted)}.adminform{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}.adminform .wide{grid-column:1/-1}.checkrow{display:flex;align-items:center;gap:8px;font-size:13px;color:#53607b}
@media(max-width:850px){.navlinks{display:none}.hero-grid{grid-template-columns:1fr;gap:35px}.cards{grid-template-columns:1fr}.hero{padding:50px 0}.section{padding:55px 0}.promo{display:block}.promo .btn{margin-top:12px}.grid2{grid-template-columns:1fr}.statgrid{grid-template-columns:repeat(3,1fr)}.adminform{grid-template-columns:1fr}.adminform .wide{grid-column:auto}}@media(max-width:520px){.wrap{width:min(100% - 28px,1160px)}.hero h1{font-size:42px}.hero p{font-size:16px}.actions .btn{padding:10px 12px}.section-head h2{font-size:30px}.dash-art{padding:12px}.stat{padding:10px}.stat strong{font-size:18px}.promo{padding:26px}}

/* Rich public homepage */
.site-header{position:sticky;top:0;z-index:20;background:rgba(255,255,255,.94);backdrop-filter:blur(16px);border-bottom:1px solid #eef1f7}
.hero-rich{padding:76px 0 84px}.hero-copy h1{max-width:680px}.hero-copy p{max-width:590px}.hero-visual{position:relative;padding:15px 0}.visual-glow{position:absolute;inset:8% -5% 0 4%;background:radial-gradient(ellipse,#cbdcff 0,transparent 68%);filter:blur(18px)}
.hero-visual .dash-art{position:relative;transform:rotate(1.2deg);padding:25px;border:1px solid #26375f}.workspace-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:11px}.workspace-tile{background:#172548;border:1px solid #2b3a5f;border-radius:15px;padding:16px 13px;min-height:150px;display:flex;flex-direction:column;gap:8px}.tile-icon{width:34px;height:34px;display:grid;place-items:center;border-radius:11px;background:#243d75;color:#8edcf2;font-size:20px}.workspace-tile small{color:#a6b5d6}.workspace-tile strong{font-size:14px;line-height:1.35}.workspace-tile em{font-size:11px;color:#9aaccf;font-style:normal}.workspace-bottom{margin-top:13px;border:1px solid #2b3a5f;background:#111f40;border-radius:15px;padding:18px;display:flex;align-items:center;justify-content:space-between;gap:12px}.workspace-bottom strong{display:block;font-size:18px;margin-top:6px}.mini-label{color:#8dacee;font-size:10px;font-weight:800;letter-spacing:.1em}.workspace-orb{width:42px;height:42px;display:grid;place-items:center;border-radius:50%;background:linear-gradient(135deg,#4bd5e8,#4169f5);font-size:21px}.floating-note{position:absolute;right:-20px;bottom:-10px;display:flex;gap:12px;align-items:center;background:#fff;border:1px solid var(--line);border-radius:15px;padding:14px 18px;box-shadow:0 18px 45px #13244b1c}.floating-check{width:30px;height:30px;border-radius:50%;background:#e6faf3;color:#158b69;display:grid;place-items:center;font-weight:900}.floating-note strong,.floating-note small{display:block}.floating-note strong{font-size:13px}.floating-note small{color:var(--muted);font-size:11px;margin-top:4px}
.domain-band{padding:55px 0 65px;background:#f8faff;border-top:1px solid #edf1fb;border-bottom:1px solid #edf1fb}.domain-wrap{display:grid;grid-template-columns:.78fr 1.22fr;gap:48px;align-items:center}.domain-intro h2{font:800 34px/1.16 Manrope;letter-spacing:-.045em;margin:17px 0 12px}.domain-intro p{color:var(--muted);line-height:1.75;margin:0;max-width:390px}.domain-box{background:#fff;border:1px solid #e2e8f5;border-radius:22px;padding:22px;box-shadow:0 20px 50px #13244b0a}.domain-tabs{display:flex;gap:8px;padding-bottom:18px;border-bottom:1px solid #edf0f7}.domain-tab{border:1px solid transparent;background:transparent;border-radius:10px;padding:11px 14px;color:#64708a;font:700 13px 'DM Sans';cursor:pointer}.domain-tab.active{background:#edf1ff;border-color:#dfe6ff;color:#3155d8}.domain-panel{padding-top:20px}.domain-panel[hidden]{display:none}.domain-search{display:flex;align-items:center;gap:0;border:1px solid #dce3f1;border-radius:13px;padding:6px;min-width:0}.domain-prefix{padding:0 0 0 12px;color:#71809c;font-size:14px}.domain-search input{flex:1;min-width:0;border:0;outline:0;padding:13px 10px;font:500 15px 'DM Sans';color:var(--ink)}.domain-exts{display:flex;align-items:center;gap:9px;flex-wrap:wrap;margin-top:14px}.domain-exts span:not(.ext-note){border:1px solid #e6eaf4;border-radius:8px;padding:6px 9px;color:#405071;font-size:12px;font-weight:700}.ext-note{color:#7a859b;font-size:12px}.domain-assurance{display:flex;gap:15px;flex-wrap:wrap;margin-top:20px;color:#68758e;font-size:11px}.domain-assurance span:before{content:'✓';color:#168b70;font-weight:900;margin-right:5px}.domain-note{background:#fff8e6;border:1px solid #f5dfa2;color:#76581a;border-radius:10px;padding:12px;font-size:12px;line-height:1.6;margin:14px 0 0}.hosting-quick{display:flex;align-items:center;justify-content:space-between;gap:20px}.hosting-quick strong{font-size:16px}.hosting-quick p{color:var(--muted);font-size:13px;line-height:1.6;margin:7px 0 0}
.section-spacious{padding:88px 0}.service-card{padding:29px;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease}.service-card:hover{transform:translateY(-4px);box-shadow:0 22px 50px #13244b10;border-color:#d6dfff}.service-kicker{display:block;color:#7583a1;font-size:10px;font-weight:800;letter-spacing:.11em;margin-top:23px}.service-card h3{margin:9px 0}.service-card p{min-height:70px}.mini-list{padding:0;margin:18px 0 8px;list-style:none;color:#53607b;font-size:12px;line-height:2}.mini-list li:before{content:'✓';color:#178c70;margin-right:8px;font-weight:900}.service-card a{border-top:1px solid #edf0f7;display:flex;justify-content:space-between;width:100%;padding-top:18px;margin-top:14px}.service-card a span{font-size:18px}.plan-label{font-size:10px;letter-spacing:.1em;color:#6c7ba0;font-weight:800;margin-top:16px}.pricecard h3{margin-top:9px}.plan-footnote{display:block;color:#7a859b;line-height:1.5;margin-top:13px}.empty-plans{grid-column:1/-1;max-width:560px;margin:auto;padding:34px}.empty-plans .btn{margin-top:10px}.plans-note{display:flex;align-items:center;gap:12px;margin-top:25px;padding:15px 18px;background:#fff;border:1px solid var(--line);border-radius:13px}.plans-note>span{width:28px;height:28px;border-radius:50%;background:#e6faf3;color:#168b70;display:grid;place-items:center;font-weight:900;flex-shrink:0}.plans-note p{flex:1;margin:0;color:var(--muted);font-size:12px;line-height:1.6}.plans-note p strong{color:var(--ink)}.plans-note a{font-size:12px;font-weight:800;color:var(--blue);white-space:nowrap}
.why-grid{display:grid;grid-template-columns:.8fr 1.2fr;gap:70px;align-items:center}.why-copy h2{font:800 38px/1.15 Manrope;letter-spacing:-.045em;margin:18px 0 15px}.why-copy p{color:var(--muted);line-height:1.8;margin-bottom:24px}.benefit-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:15px}.benefit{border:1px solid var(--line);border-radius:16px;padding:22px;background:#fff}.benefit>span{font-size:11px;color:var(--blue);font-weight:900;letter-spacing:.1em}.benefit h3{font-size:17px;margin:13px 0 7px}.benefit p{font-size:12px;color:var(--muted);line-height:1.7;margin:0}
.commitment-section{padding-top:15px}.commitment{display:grid;grid-template-columns:auto 1fr 1fr;align-items:center;gap:25px;padding:35px;border:1px solid #e0e7f8;border-radius:22px;background:linear-gradient(120deg,#f8faff,#fff)}.commitment-mark{width:58px;height:58px;border-radius:18px;background:#e6faf3;color:#158b69;display:grid;place-items:center;font-size:28px;font-weight:900}.commitment-copy h2{font:800 25px/1.2 Manrope;letter-spacing:-.04em;margin:12px 0}.commitment-copy p{font-size:13px;line-height:1.7;color:var(--muted);margin:0}.commitment-points{display:grid;gap:14px;border-left:1px solid #e1e7f2;padding-left:25px}.commitment-points strong,.commitment-points span{display:block}.commitment-points strong{font-size:13px}.commitment-points span{font-size:11px;color:var(--muted);margin-top:3px}
.steps{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.step{padding:26px;border:1px solid var(--line);border-radius:17px;background:#fff}.step-number{font:800 28px Manrope;color:#4169f5;background:#edf1ff;border-radius:12px;width:55px;height:55px;display:grid;place-items:center}.step h3{font-size:18px;margin:20px 0 8px}.step p{font-size:13px;color:var(--muted);line-height:1.7;margin:0}.final-cta{padding:10px 0 78px}.final-cta-inner{display:flex;justify-content:space-between;align-items:center;gap:30px;padding:38px 42px;border-radius:22px;background:linear-gradient(120deg,#f1f5ff,#e9f4ff);border:1px solid #e0e9ff}.final-cta h2{font:800 30px/1.2 Manrope;letter-spacing:-.04em;margin:13px 0 8px}.final-cta p{color:var(--muted);font-size:13px;line-height:1.6;margin:0}.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
@media(max-width:900px){.domain-wrap,.why-grid{grid-template-columns:1fr;gap:28px}.domain-intro p{max-width:650px}.commitment{grid-template-columns:auto 1fr}.commitment-points{grid-column:1/-1;border-left:0;border-top:1px solid #e1e7f2;padding:20px 0 0;grid-template-columns:repeat(3,1fr)}.final-cta-inner{align-items:flex-start;flex-direction:column}.hero-rich{padding:55px 0 65px}}
@media(max-width:600px){.hero-visual .dash-art{padding:13px}.workspace-grid{grid-template-columns:1fr}.workspace-tile{min-height:0;display:grid;grid-template-columns:34px 1fr;align-items:center;gap:5px 10px}.workspace-tile .tile-icon{grid-row:span 2}.workspace-tile small{align-self:end}.workspace-tile strong{align-self:start}.workspace-tile em{display:none}.floating-note{right:-4px;bottom:-25px}.domain-band{padding:42px 0}.domain-box{padding:14px}.domain-search{flex-wrap:wrap}.domain-search input{width:calc(100% - 60px);flex:auto}.domain-search .btn{width:100%;margin-top:5px}.domain-assurance{display:grid;gap:9px}.hosting-quick{align-items:flex-start;flex-direction:column}.section-spacious{padding:58px 0}.service-card p{min-height:0}.benefit-grid,.steps{grid-template-columns:1fr}.commitment{grid-template-columns:1fr;padding:24px}.commitment-points{grid-template-columns:1fr}.final-cta-inner{padding:28px 22px}.final-cta h2{font-size:25px}.plans-note{align-items:flex-start;flex-wrap:wrap}.plans-note p{min-width:75%}}


/* Drypp visual refresh: persistent domain search and richer dark/light rhythm */
:root{--navy:#08132d;--blue:#4b70ff;--cyan:#4bd5e8;--ink:#121e3a;--muted:#aebbd5;--line:#293754;--pale:#101d39}
body{background:#0b1530;color:#eaf0ff}
.global-domain-bar{position:sticky;top:0;z-index:1000;background:linear-gradient(100deg,#071127,#12234b);border-bottom:1px solid #29395e;box-shadow:0 8px 24px #02081730}
.global-domain-inner{min-height:76px;display:grid;grid-template-columns:250px minmax(260px,1fr) auto;align-items:center;gap:16px}
.global-domain-prompt{display:flex;align-items:center;gap:11px;color:#f7f9ff}.global-domain-icon{display:grid;place-items:center;width:39px;height:39px;border-radius:12px;background:#1b2d56;color:#67ddec;font-size:25px}.global-domain-prompt strong,.global-domain-prompt small{display:block}.global-domain-prompt strong{font-size:13px}.global-domain-prompt small{font-size:11px;color:#9eafd0;margin-top:3px}
.global-domain-form{display:flex;gap:8px;padding:6px;border-radius:14px;background:#fff;box-shadow:0 7px 24px #0002}.global-domain-form input{min-width:0;flex:1;border:0;outline:0;padding:10px 13px;border-radius:9px;font:500 14px 'DM Sans',sans-serif;color:#14203b;background:#fff}.global-domain-submit{border:0;border-radius:10px;background:linear-gradient(120deg,#4169f5,#3560ec);color:white;font:700 13px 'DM Sans',sans-serif;padding:0 17px;cursor:pointer;white-space:nowrap}.global-domain-note{font-size:11px;color:#a9badb;text-align:right}.global-tools{display:flex;justify-content:flex-end;align-items:center;gap:8px}.global-tools select,.theme-toggle{border:1px solid #344568;background:#12234b;color:#eaf1ff;border-radius:9px;padding:9px 10px;font:600 11px 'DM Sans';cursor:pointer}.theme-toggle{white-space:nowrap}.header-select{border:1px solid #344568;background:#12234b;color:#eaf1ff;border-radius:9px;padding:9px 10px;font:600 11px 'DM Sans';cursor:pointer}.domain-after-copy{margin:18px 0 0;color:var(--muted);line-height:1.7;font-size:14px}.currency-hint{font-size:12px;color:var(--muted);margin-top:8px}
.site-header{background:#0c1935;color:#eaf0ff;border-bottom:1px solid #233253}.brand{color:#fff}.navlinks{color:#c0cce4}.btn-light{background:#162546;color:#f4f7ff;border-color:#344467}.btn-primary{background:linear-gradient(120deg,#4c70ff,#3558e8)}
.hero{background:radial-gradient(circle at 82% 10%,#1c3971 0,transparent 32%),radial-gradient(circle at 10% 90%,#102954 0,transparent 38%),linear-gradient(130deg,#08132d,#101f40 65%,#0b1733);color:#f5f7ff}.hero p,.proof{color:#b7c4df}.eyebrow{background:#1c315d;color:#93aaff}.hero-trust{color:#c2cfe7}.dash-art{background:#050d22;border:1px solid #27395f}.floating-note{background:#132344;border:1px solid #2a3e65;color:#eef3ff}.floating-note small{color:#b4c3df}
.domain-band{background:linear-gradient(140deg,#111f3d,#0b1530);border-top:1px solid #273757;border-bottom:1px solid #273757;color:#f4f7ff}.domain-intro p{color:#b4c2dc}.domain-box{background:#111f3d;border-color:#344568;box-shadow:0 25px 60px #0003}.domain-tabs{background:#09142c}.domain-tab{color:#bdc9e1}.domain-tab.active{background:#223965;color:#fff}.domain-search{background:#fff}.domain-assurance,.domain-assurance span{color:#b9c7e0}
.section{background:#0b1530;color:#edf2ff}.section:nth-of-type(even){background:#101e3c}.section-head p{color:#b4c1dc}.card,.service-card,.benefit,.step{background:#122141;border-color:#293b5f;color:#f1f5ff;box-shadow:0 15px 38px #0002}.card p,.service-card p,.benefit p,.step p{color:#b4c2dd}.service-kicker{color:#91a5cf}.mini-list{color:#c0cce3}.iconbox,.step-number{background:#20345e;color:#91a8ff}.why-grid{color:#f0f4ff}.commitment{background:linear-gradient(120deg,#14264a,#0e1b36);border-color:#2d4168}.commitment-copy p{color:#bac8e1}.commitment-points{border-color:#2e4166}.commitment-points strong{color:#f1f5ff}.commitment-points span{color:#aebdd9}.final-cta-inner{background:radial-gradient(circle at 90% 0,#244887 0,transparent 45%),linear-gradient(120deg,#101f40,#172d59);border-color:#2e4778;color:#fff}.final-cta p{color:#c1cee5}.footer{background:#050d20;border-top:1px solid #25365a;color:#aebbd4}.footer strong{color:#fff!important}
/* Preserve contrast on all screen sizes */
@media(max-width:900px){.global-domain-inner{grid-template-columns:1fr;gap:9px;padding-top:12px;padding-bottom:12px}.global-domain-prompt{display:none}.global-domain-note{text-align:left}.global-domain-form{width:100%}.global-domain-bar{position:sticky}}
@media(max-width:600px){.global-domain-inner{width:calc(100% - 24px)}.global-domain-form{gap:5px}.global-domain-form input{padding:10px 8px;font-size:13px}.global-domain-submit{padding:0 10px;font-size:12px}.nav{height:auto;min-height:70px;flex-wrap:wrap;gap:10px;padding:12px 0}.navlinks{order:3;width:100%;overflow-x:auto;gap:18px;padding:4px 0}.actions .btn{padding:9px 11px;font-size:12px}}


/* Gradient-rich homepage finish: keep the dark brand while adding depth and visual rhythm. */
.hero{position:relative;background:radial-gradient(ellipse at 82% 18%,rgba(65,105,245,.30),transparent 38%),radial-gradient(ellipse at 12% 88%,rgba(75,213,232,.12),transparent 35%),linear-gradient(128deg,#071127 0%,#101f42 48%,#0a1530 100%)}
.hero:after{content:"";position:absolute;inset:0;pointer-events:none;background:linear-gradient(90deg,transparent 0%,rgba(76,112,255,.035) 50%,transparent 100%)}
.domain-band{background:radial-gradient(ellipse at 90% 5%,rgba(57,105,212,.22),transparent 40%),linear-gradient(135deg,#13264a 0%,#0a1530 70%,#101f40 100%)}
.section{position:relative;background:linear-gradient(145deg,#0b1530 0%,#0d1935 55%,#0a142c 100%)}
.section:nth-of-type(even){background:radial-gradient(ellipse at 0% 20%,rgba(58,99,190,.14),transparent 36%),linear-gradient(125deg,#101f40 0%,#132448 50%,#0c1935 100%)}
.section-head h2,.why-copy h2{background:linear-gradient(100deg,#fff 0%,#dce7ff 58%,#8edcf2 100%);-webkit-background-clip:text;background-clip:text;color:transparent}
.card,.service-card,.benefit,.step{background:linear-gradient(145deg,rgba(22,41,77,.98),rgba(14,28,55,.98));border-color:rgba(117,148,210,.22);box-shadow:0 18px 44px rgba(0,0,0,.18)}
.card:hover,.service-card:hover,.benefit:hover,.step:hover{border-color:rgba(91,132,255,.58);box-shadow:0 22px 55px rgba(31,76,170,.2)}
.pricecard.featured{background:radial-gradient(ellipse at 100% 0%,rgba(69,110,255,.2),transparent 45%),linear-gradient(145deg,#182e58,#10203f);border-color:#557bff}
.price{color:#fff}.features li{padding:1px 0}.features li small{color:#8fa4ca;font-size:10px}
.plans-note{background:linear-gradient(120deg,#13264a,#0e1c38);border-color:#2c4168}.plans-note p{color:#b9c8e3}.plans-note p strong{color:#fff}.plans-note a{color:#8fb0ff}
.commitment{background:radial-gradient(ellipse at 100% 0%,rgba(67,107,240,.2),transparent 42%),linear-gradient(120deg,#14264a,#0b1832)}
.final-cta-inner{position:relative;overflow:hidden;background:radial-gradient(ellipse at 85% 0%,rgba(75,213,232,.2),transparent 38%),linear-gradient(120deg,#142957 0%,#1c3977 55%,#10234b 100%);box-shadow:0 24px 60px rgba(0,0,0,.2)}
.eyebrow{box-shadow:inset 0 0 0 1px rgba(130,161,255,.14)}
.global-domain-bar{background:linear-gradient(100deg,#050d20 0%,#10234b 55%,#0b1936 100%)}
@media(prefers-reduced-motion:no-preference){.card,.service-card,.benefit,.step{transition:transform .2s ease,border-color .2s ease,box-shadow .2s ease}.service-card:hover,.benefit:hover,.step:hover{transform:translateY(-3px)}}


/* Accessible light/dark theme switch */
html[data-theme="light"] body{background:#f7f9ff;color:#14203b}
html[data-theme="light"] .hero{background:radial-gradient(circle at 85% 12%,#c9dcff 0,transparent 32%),linear-gradient(130deg,#f8faff,#e8f0ff)}
html[data-theme="light"] .section,html[data-theme="light"] .section:nth-of-type(even){background:linear-gradient(135deg,#f8faff,#eaf1ff);color:#14203b}
html[data-theme="light"] .section-head h2,html[data-theme="light"] .why-copy h2{background:none;color:#14203b}
html[data-theme="light"] .card,html[data-theme="light"] .service-card,html[data-theme="light"] .benefit,html[data-theme="light"] .step{background:#fff;color:#14203b;border-color:#dce4f4}
html[data-theme="light"] .card p,html[data-theme="light"] .service-card p,html[data-theme="light"] .benefit p,html[data-theme="light"] .step p,html[data-theme="light"] .features,html[data-theme="light"] .mini-list{color:#52617c}
html[data-theme="light"] .domain-band{background:linear-gradient(140deg,#f1f5ff,#e2ebff);color:#14203b}
html[data-theme="light"] .domain-box{background:#fff;border-color:#dce4f4}
html[data-theme="light"] .domain-tabs{background:#f4f6ff}html[data-theme="light"] .domain-tab{color:#52617c}
html[data-theme="light"] .commitment{background:linear-gradient(120deg,#fff,#eaf1ff);border-color:#dce4f4}
html[data-theme="light"] .commitment-copy p,html[data-theme="light"] .commitment-points span{color:#52617c}html[data-theme="light"] .commitment-points strong{color:#14203b}
html[data-theme="light"] .price{color:#14203b}html[data-theme="light"] .pricecard.featured{background:linear-gradient(145deg,#fff,#e6edff)}
html[data-theme="light"] .plans-note{background:#fff;border-color:#dce4f4}html[data-theme="light"] .plans-note p{color:#52617c}html[data-theme="light"] .plans-note p strong{color:#14203b}
html[data-theme="light"] .site-header{background:rgba(255,255,255,.94)}html[data-theme="light"] .navlinks{color:#46516b}html[data-theme="light"] .brand{color:#0b1530}
html[data-theme="light"] .global-domain-bar{background:linear-gradient(100deg,#eaf1ff,#f9fbff);border-color:#dce4f4}html[data-theme="light"] .global-domain-prompt{color:#14203b}html[data-theme="light"] .global-domain-prompt small{color:#65708a}html[data-theme="light"] .global-tools select,html[data-theme="light"] .theme-toggle,html[data-theme="light"] .header-select{background:#fff;color:#14203b;border-color:#dce4f4}
html[data-theme="light"] .final-cta-inner{color:#fff}html[data-theme="light"] .footer{background:#101a32;color:#c3cde0}
@media(max-width:900px){.global-tools{justify-content:flex-start}.global-domain-inner{grid-template-columns:1fr}}

/* Compact full-width domain search: title above the search field. */
.global-domain-inner{display:block;padding-top:12px;padding-bottom:13px;min-height:0;width:min(1320px,calc(100% - 48px))}
.global-domain-heading{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:9px}
.global-domain-prompt{gap:10px}.global-domain-prompt strong{font-size:14px}.global-domain-prompt small{font-size:11px}
.global-domain-form{width:100%;min-height:47px;align-items:center;border:1px solid #dce5fb;padding:5px 6px 5px 14px}
.global-domain-www{font-size:13px;color:#71809c;padding-right:4px}.global-domain-form input{font-size:15px;padding:10px 8px}
.global-domain-submit{min-height:37px;padding:0 22px}
.global-domain-bar{position:sticky;top:0}
/* Less vertical repetition: retain core services, plans, benefits, steps and final CTA. */
.section,.section-spacious{padding-top:54px;padding-bottom:54px}
.section-head{margin-bottom:25px}.section-head h2{font-size:34px}.service-card{padding:23px}.service-card p{min-height:0}.mini-list{margin-top:12px}.pricecard{padding:23px}.features{margin:14px 0;line-height:1.8}
.pricecard.featured{transform:translateY(-5px);z-index:1;overflow:visible}
.pricecard.featured:before{content:'MOST POPULAR · SAVE';position:absolute;left:50%;top:-13px;transform:translateX(-50%);white-space:nowrap;background:linear-gradient(100deg,#4bd5e8,#4b70ff);color:#07132d;padding:7px 15px;border-radius:7px;font-size:10px;font-weight:900;letter-spacing:.07em;box-shadow:0 7px 18px #0003}
.pricecard.featured .pill{display:none}
@media(max-width:900px){.global-domain-inner{display:block;padding-top:10px;padding-bottom:11px}.global-domain-heading{align-items:flex-start}.global-domain-prompt{display:flex}.global-tools{flex-shrink:0}.section,.section-spacious{padding-top:44px;padding-bottom:44px}.pricecard.featured{transform:none}}
@media(max-width:600px){.global-domain-inner{width:calc(100% - 24px)}.global-domain-heading{gap:8px;align-items:center}.global-domain-prompt{gap:7px}.global-domain-icon{width:30px;height:30px;font-size:19px}.global-domain-prompt strong{font-size:12px}.global-domain-prompt small{font-size:10px;max-width:210px}.global-tools{gap:5px}.global-tools select,.theme-toggle{padding:7px 6px;font-size:10px}.theme-toggle span{display:none}.global-domain-form{min-height:43px;padding-left:9px}.global-domain-form input{font-size:13px;padding:8px 5px}.global-domain-submit{padding:0 10px;font-size:11px}.global-domain-www{font-size:11px}.section-head h2{font-size:29px}}


.domain-band-inline{padding:34px 0 38px}.domain-band-inline .wrap{display:grid;grid-template-columns:.72fr 1.28fr;gap:28px;align-items:center}.domain-inline-heading h2{font:800 30px/1.15 Manrope;letter-spacing:-.04em;margin:12px 0}.domain-inline-heading p{max-width:460px;color:#b4c2dc;line-height:1.65;margin:0}.domain-inline-box{background:#0d1b36;border:1px solid #344568;border-radius:18px;padding:18px;box-shadow:0 18px 42px #0002}.domain-search-form{display:flex;align-items:center;gap:6px;background:#fff;border:1px solid #dce3f1;border-radius:13px;padding:6px}.domain-search-form input{flex:1;min-width:0;border:0;outline:0;padding:12px 8px;color:#14203b;font:500 15px 'DM Sans'}.domain-search-form .domain-prefix{padding-left:10px;color:#71809c}.domain-search-notice{font-size:12px;line-height:1.5;color:#b9c7e0;margin:12px 0 0}.header-select{padding:8px 7px}.actions{gap:7px}.navlinks{gap:18px}
html[data-theme="light"] .domain-inline-box{background:#fff;border-color:#dce4f4}html[data-theme="light"] .domain-inline-heading p{color:#52617c}
@media(max-width:900px){.domain-band-inline .wrap{grid-template-columns:1fr;gap:18px}.domain-inline-heading h2{font-size:28px}.actions{flex-wrap:wrap}}
@media(max-width:600px){.domain-band-inline{padding:30px 0}.domain-inline-box{padding:12px}.domain-search-form{flex-wrap:wrap}.domain-search-form input{width:calc(100% - 75px);flex:1}.domain-search-form .btn{width:100%;justify-content:center}.header-select{font-size:10px;padding:7px 5px}.actions{gap:5px}}

/* Domain search: single-column, full-width and high-contrast extensions. */
.domain-band-inline{padding:28px 0 34px}
.domain-band-inline .wrap{display:block;width:min(1320px,calc(100% - 48px));max-width:1320px}
.domain-inline-heading{display:none!important}
.domain-inline-box{width:100%;max-width:none;padding:22px 24px;background:radial-gradient(ellipse at 100% 0%,rgba(75,213,232,.12),transparent 38%),linear-gradient(135deg,#13264a 0%,#0d1a35 75%);border-color:#3d5682}
.domain-search-form{width:100%;min-height:64px;padding:7px 8px 7px 16px;gap:10px;border-radius:15px}
.domain-search-form input{font-size:17px;padding:15px 10px}
.domain-search-form .domain-prefix{font-size:14px;color:#536582}
.domain-search-form .btn{min-height:46px;padding:0 22px;white-space:nowrap}
.domain-exts{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:15px}
.domain-exts span:not(.ext-note){display:inline-flex;align-items:center;justify-content:center;min-height:34px;padding:7px 12px;border:1px solid #7e9ac9;border-radius:9px;background:#1b3158;color:#f0f5ff;font-size:13px;font-weight:800;letter-spacing:.02em}
.ext-note{color:#d0dcf3;font-size:13px;font-weight:500}
.domain-assurance{display:flex;gap:20px;flex-wrap:wrap;margin-top:17px;color:#d4e0f5;font-size:12px}
.domain-assurance span:before{content:'✓';color:#4bd5b0;font-weight:900;margin-right:6px}
html[data-theme="light"] .domain-inline-box{background:radial-gradient(ellipse at 100% 0%,rgba(75,120,255,.12),transparent 38%),linear-gradient(135deg,#fff 0%,#edf3ff 100%);border-color:#cbd8f0}
html[data-theme="light"] .domain-exts span:not(.ext-note){background:#e7eeff;border-color:#b6c7ef;color:#203963}
html[data-theme="light"] .ext-note{color:#52617c}
html[data-theme="light"] .domain-assurance{color:#52617c}
@media(max-width:900px){.domain-band-inline .wrap{width:calc(100% - 32px)}.domain-inline-box{padding:18px}}
@media(max-width:600px){.domain-band-inline{padding:22px 0 26px}.domain-inline-box{padding:12px}.domain-search-form{min-height:0;gap:5px;padding:5px;flex-wrap:wrap}.domain-search-form input{font-size:14px;padding:11px 5px;width:calc(100% - 70px);flex:1}.domain-search-form .btn{width:100%;min-height:43px;margin-top:3px}.domain-exts{gap:7px}.domain-exts span:not(.ext-note){min-height:30px;padding:5px 9px;font-size:12px}.ext-note{font-size:12px}.domain-assurance{display:grid;gap:9px;font-size:11px}}

/* Compact first viewport: bring the domain search and its button into view sooner. */
.hero{padding-top:34px!important;padding-bottom:36px!important}
.hero-rich{padding-top:34px!important;padding-bottom:36px!important}
.hero-grid{gap:34px}
.hero-visual{padding-top:3px!important;padding-bottom:3px!important}
.hero-actions{margin-top:18px}
.hero h1{margin-top:13px;margin-bottom:12px}
.hero-copy p{margin-top:0;margin-bottom:0;line-height:1.58}
.hosting-section-head{margin-bottom:22px}
.hosting-section-head h2{margin:0 0 7px!important}
.hosting-section-head p{margin:0 auto;line-height:1.45}
/* The featured card's generic card-link rule must not override CTA contrast. */
.pricecard a.btn.btn-primary,.pricecard.featured a.btn.btn-primary{color:#fff!important;background:linear-gradient(120deg,#5278ff,#3558e8)!important;border-color:#7390ff!important;text-shadow:none!important;opacity:1!important}
.pricecard.featured a.btn.btn-light{color:#17264a!important;background:#fff!important;border-color:#d8e2fa!important}
.pricecard .btn{min-height:44px;font-weight:800;letter-spacing:.005em}
html[data-theme="light"] .pricecard a.btn.btn-primary{color:#fff!important}
@media(max-width:850px){.hero,.hero-rich{padding-top:26px!important;padding-bottom:28px!important}.hero-grid{gap:20px}.hero-visual{padding:0!important}}
@media(max-width:520px){.hero,.hero-rich{padding-top:20px!important;padding-bottom:22px!important}.hero h1{margin-top:10px;font-size:36px}.hero-actions{margin-top:14px}.hosting-section-head h2{font-size:27px!important}}


/* Drypp admin hosting control centre: isolate admin colours from the public theme. */
.admin-hosting-page,
html[data-theme="dark"] .admin-hosting-page,
html[data-theme="light"] .admin-hosting-page {
    --admin-ink: #17233f;
    --admin-muted: #5f6d86;
    --admin-line: #dce4f0;
    --admin-blue: #4169f5;
    --admin-navy: #142344;
    background: #f3f6fc !important;
    color: var(--admin-ink) !important;
    min-height: 100vh;
    font-family: 'DM Sans', sans-serif;
}
.admin-hosting-page * { box-sizing: border-box; }
.admin-hosting-page .appbar { background: #fff !important; border-bottom: 1px solid var(--admin-line) !important; box-shadow: 0 2px 12px rgba(20,35,68,.04); }
.admin-hosting-page .appnav { min-height: 68px; height: auto; gap: 18px; }
.admin-hosting-page .brand { color: var(--admin-navy) !important; }
.admin-hosting-page .appnav .navlinks { display:flex; color: #52617c !important; }
.admin-hosting-page .appnav .navlinks a { color: #52617c !important; font-weight: 700; }
.admin-hosting-page .appnav .navlinks a:hover { color: var(--admin-blue) !important; }
.admin-hosting-page .content { padding-top: 34px; padding-bottom: 54px; }
.admin-hosting-page .content > h1 { color: var(--admin-navy) !important; font-size: clamp(27px,3vw,36px); line-height:1.15; letter-spacing:-.04em; }
.admin-hosting-page .content > p { color: var(--admin-muted) !important; line-height:1.6; max-width:850px; }
.admin-hosting-page .muted,
.admin-hosting-page small.muted { color: var(--admin-muted) !important; }
.admin-hosting-page .cards { grid-template-columns: repeat(3,minmax(0,1fr)); gap:16px; }
.admin-hosting-page .cards > .card { background: #fff !important; color: var(--admin-ink) !important; border:1px solid var(--admin-line) !important; border-radius:16px; padding:20px 22px; box-shadow:0 8px 24px rgba(20,35,68,.055) !important; }
.admin-hosting-page .cards > .card .muted { color:var(--admin-muted) !important; font-size:13px; font-weight:700; }
.admin-hosting-page .cards > .card .price { color:var(--admin-navy) !important; font:800 32px/1.1 Manrope,sans-serif; margin:12px 0 0; }
.admin-hosting-page .panel { background:#fff !important; color:var(--admin-ink) !important; border:1px solid var(--admin-line) !important; border-radius:16px; padding:22px; box-shadow:0 8px 24px rgba(20,35,68,.045) !important; margin-bottom:18px; }
.admin-hosting-page .panel h2 { color:var(--admin-navy) !important; font:800 19px/1.3 Manrope,sans-serif; letter-spacing:-.025em; margin:0 0 18px !important; }
.admin-hosting-page .field { min-width:0; margin-bottom:4px; }
.admin-hosting-page .form-label { color:#40506d !important; font-size:12px; font-weight:700; line-height:1.4; }
.admin-hosting-page .form-control { min-height:44px; padding:10px 12px; border:1px solid #d6dfed !important; border-radius:9px; background:#fff !important; color:#17233f !important; font:500 13px 'DM Sans',sans-serif; box-shadow:0 1px 2px rgba(20,35,68,.025); }
.admin-hosting-page .form-control::placeholder { color:#8b97aa !important; opacity:1; }
.admin-hosting-page .form-control:focus { outline:0; border-color:#6b89ff !important; box-shadow:0 0 0 3px rgba(65,105,245,.13) !important; }
.admin-hosting-page .adminform { column-gap:14px; row-gap:12px; }
.admin-hosting-page .checkrow { color:#465571 !important; font-size:12px; line-height:1.45; }
.admin-hosting-page input[type="checkbox"] { accent-color:var(--admin-blue); width:15px; height:15px; }
.admin-hosting-page .btn { min-height:40px; border-radius:9px; padding:10px 15px; font-size:12px; font-weight:800; }
.admin-hosting-page .btn-primary { background:var(--admin-blue) !important; color:#fff !important; border-color:var(--admin-blue) !important; box-shadow:0 5px 13px rgba(65,105,245,.18); }
.admin-hosting-page .btn-light { background:#fff !important; color:#263858 !important; border:1px solid #d5deec !important; }
.admin-hosting-page .btn:hover { transform:translateY(-1px); }
.admin-hosting-page .table-wrap { width:100%; border:1px solid #e5eaf2; border-radius:10px; }
.admin-hosting-page table { min-width:650px; background:#fff !important; color:#263650 !important; font-size:12px; }
.admin-hosting-page th { background:#f5f7fb !important; color:#53627b !important; font-size:10px; font-weight:800; letter-spacing:.055em; padding:12px 11px; border-bottom:1px solid #dce4f0 !important; white-space:nowrap; }
.admin-hosting-page td { color:#34435e !important; padding:12px 11px; border-bottom:1px solid #e9edf4 !important; vertical-align:middle; }
.admin-hosting-page td strong { color:#1b2a47 !important; }
.admin-hosting-page tr:last-child td { border-bottom:0 !important; }
.admin-hosting-page .status { background:#edf2ff !important; color:#3154c9 !important; border:1px solid #dce5ff; border-radius:7px; padding:5px 8px; }
.admin-hosting-page .notice { background:#effaf5 !important; color:#176b4b !important; border:1px solid #c9efdf; }
.admin-hosting-page .grid2 { align-items:stretch; gap:18px; }
.admin-hosting-page .grid2 > .panel { min-width:0; }
.admin-hosting-page .grid2:last-child { align-items:stretch; }
@media(max-width:850px) {
    .admin-hosting-page .content { padding-top:24px; }
    .admin-hosting-page .cards { grid-template-columns:repeat(3,minmax(0,1fr)); }
    .admin-hosting-page .grid2 { grid-template-columns:1fr; }
}
@media(max-width:600px) {
    .admin-hosting-page .appnav { padding-top:10px; padding-bottom:10px; flex-wrap:wrap; }
    .admin-hosting-page .appnav .navlinks { order:3; width:100%; justify-content:flex-start; }
    .admin-hosting-page .content { padding-top:20px; }
    .admin-hosting-page .cards { grid-template-columns:1fr; gap:10px; }
    .admin-hosting-page .cards > .card { padding:16px 18px; }
    .admin-hosting-page .panel { padding:16px; }
    .admin-hosting-page .adminform { grid-template-columns:1fr; }
    .admin-hosting-page .adminform .wide { grid-column:auto; }
}

</style>
</head>
<body>
@yield('body')
<script>
(function(){
  var html=document.documentElement, themeButton=document.getElementById('theme-toggle');
  function setTheme(theme){html.setAttribute('data-theme',theme);if(themeButton){themeButton.innerHTML=theme==='dark'?'☼ <span>Light mode</span>':'☾ <span>Dark mode</span>';}}
  var savedTheme='dark';try{savedTheme=localStorage.getItem('drypp-theme')||'dark';}catch(e){} setTheme(savedTheme);
  if(themeButton) themeButton.addEventListener('click',function(){var next=html.getAttribute('data-theme')==='dark'?'light':'dark';setTheme(next);try{localStorage.setItem('drypp-theme',next);}catch(e){}});
  var currency=document.getElementById('display-currency');
  var rates={USD:1,NGN:1500,GBP:0.78,EUR:0.91}, symbols={USD:'$',NGN:'₦',GBP:'£',EUR:'€'};
  if(currency){currency.addEventListener('change',function(){document.querySelectorAll('[data-price-usd]').forEach(function(el){var usd=parseFloat(el.getAttribute('data-price-usd')||'0');var val=usd*rates[currency.value];el.textContent=symbols[currency.value]+(currency.value==='NGN'?Math.round(val).toLocaleString('en-US'):val.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));});});}

})();
</script>
<footer class="footer"><div class="wrap footer-row"><div><strong style="font:800 19px Manrope;color:white">Drypp</strong><div style="margin-top:8px">Tools to host, market and grow your business.</div></div><div>© {{ date('Y') }} Drypp · A Commodots product</div></div></footer></body></html>

