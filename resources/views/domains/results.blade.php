@extends('public.layout')
@section('title', 'Choose Domain & Hosting — Drypp')
@section('body')
<header class="site-header">
    <div class="wrap nav">
        <a class="brand" href="{{ route('home') }}"><span class="drop">◒</span>drypp<span style="color:var(--blue)">.</span></a>
        <div class="actions">
            <a class="btn btn-light" href="{{ route('login') }}">Log in</a>
            <a class="btn btn-primary" href="{{ route('register') }}">Create account ↗</a>
        </div>
    </div>
</header>

<main class="wrap" style="max-width:1120px;padding:36px 20px 80px">
    <div class="checkout-steps">
        <div class="step active"><strong>1. Domain</strong><small>Search & select</small></div>
        <div class="step" id="step-hosting"><strong>2. Hosting</strong><small>Choose a package</small></div>
        <div class="step" id="step-cart"><strong>3. Cart & account</strong><small>Review and continue</small></div>
    </div>

    <span class="eyebrow">Domain search</span>
    <h1 style="margin:8px 0 12px">Find your domain</h1>
    <form action="{{ route('domains.search') }}" method="get" class="domain-search-form">
        <input class="form-control" name="domain" value="{{ $domain }}" required aria-label="Domain name">
        <button class="btn btn-primary" type="submit">Search again</button>
    </form>

    @if($errors->any())
        <div class="notice" style="margin-bottom:18px">{{ $errors->first() }}</div>
    @endif

    <section id="domain-panel" class="card domain-results" style="padding:0;overflow:hidden">
        <div class="section-heading">
            <strong>Domain options for “{{ $domain }}”</strong>
            <p class="muted">USD prices include the configured Drypp markup. We check a wider range of Porkbun-supported extensions and show available alternatives first.</p>
        </div>
        @forelse($results as $result)
            <div class="domain-row">
                <div>
                    <strong class="domain-name">{{ $result['domain'] }}</strong>
                    <div class="domain-state">
                        @if($result['available'])
                            <span class="available">Available</span>
                        @else
                            <span class="taken">Taken</span>
                        @endif
                        @if($result['premium'])<span class="muted"> · Premium domain</span>@endif
                        @if($result['sandbox'])<span class="muted"> · Sandbox result</span>@endif
                    </div>
                    @if($result['renewal_usd'] !== null)
                        <small class="muted">Estimated renewal: ${{ number_format($result['renewal_usd'], 2) }}/year</small>
                    @endif
                </div>
                <div class="domain-actions">
                    @if($result['drypp_price_usd'] !== null && (float) $result['drypp_price_usd'] > 0)
                        <strong class="domain-price">${{ number_format((float)$result['drypp_price_usd'], 2) }} <small>/ first year USD</small></strong>
                    @else
                        <strong class="domain-price">Price unavailable</strong>
                    @endif
                    @if($result['available'] && $result['drypp_price_usd'] !== null && (float) $result['drypp_price_usd'] > 0)
                        <button class="btn btn-primary js-domain-select" type="button"
                            data-domain="{{ $result['domain'] }}"
                            data-price="{{ (float)$result['drypp_price_usd'] }}"
                            data-renewal="{{ (float)($result['renewal_usd'] ?? 0) }}">
                            Select domain
                        </button>
                    @elseif($result['available'])
                        <button class="btn btn-light" type="button" disabled>Pricing unavailable</button>
                    @else
                        <button class="btn btn-light" type="button" disabled>Unavailable</button>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-results"><strong>No verified domain options found.</strong><p class="muted">Porkbun did not return confirmed availability for these names. Try another search or check again shortly.</p></div>
        @endforelse
    </section>

    <section id="hosting-panel" class="card flow-panel" hidden>
        <div class="panel-heading">
            <div><span class="eyebrow">Step 2</span><h2>Choose hosting</h2><p class="muted" id="chosen-domain-summary"></p></div>
            <button type="button" class="btn btn-light" id="edit-domain">Change domain</button>
        </div>
        <div class="hosting-grid">
            @forelse($packages as $package)
                @php $isPopular = abs((float)($package->price_usd ?? 0) - 49.99) < 0.01; @endphp
                <article class="hosting-option {{ $isPopular ? 'hosting-option-popular' : '' }}">
                    @if($isPopular)
                        <span class="popular-badge">MOST POPULAR</span>
                    @endif
                    <h3>{{ $package->name }}</h3>
                    <p class="muted">{{ $package->description }}</p>
                    <div class="hosting-price">${{ number_format((float)($package->price_usd ?? 0), 2) }} <small>/ {{ $package->billing_cycle ?? 'year' }}</small></div>
                    <ul>
                        <li>{{ $package->disk_space_gb ?? 0 }} GB storage</li>
                        <li>{{ $package->bandwidth_gb ?? 0 }} GB bandwidth</li>
                        <li>{{ $package->email_accounts ?? 0 }} email accounts</li>
                        <li>{{ $package->databases ?? 0 }} databases</li>
                        @if($package->free_website_enabled)
                            <li><strong>Free website</strong> included</li>
                        @endif
                    </ul>
                    <button class="btn btn-primary js-hosting-select" type="button"
                        data-package="{{ $package->id }}"
                        data-name="{{ $package->name }}"
                        data-price="{{ (float)($package->price_usd ?? 0) }}"
                        data-cycle="{{ $package->billing_cycle ?? 'year' }}">Select hosting</button>
                </article>
            @empty
                <div class="empty-results"><strong>No hosting packages are active.</strong><p class="muted">You can continue with the domain only.</p></div>
            @endforelse
        </div>
        <button type="button" class="btn btn-light" id="domain-only" style="margin-top:16px">Continue with domain only</button>
    </section>

    <section id="cart-panel" class="card flow-panel" hidden>
        <div class="panel-heading"><div><span class="eyebrow">Step 3</span><h2>Your cart</h2><p class="muted">Review your selection, then sign in or create an account to continue.</p></div><button type="button" class="btn btn-light" id="back-hosting">Edit selection</button></div>
        <div class="checkout-grid">
            <div class="cart-column">
                <div id="cart-lines"></div>
                <div class="cart-total"><span>Estimated total</span><strong id="cart-total">$0.00</strong></div>
                <p class="muted small-note">Prices are estimates in USD. Final availability and prices will be checked again before payment. Domain registration is not completed until payment and registrar confirmation.</p>
            </div>
            <aside class="account-box">
                <div class="account-tabs" role="tablist" aria-label="Account access">
                    <button type="button" class="account-tab active" id="tab-register" role="tab" aria-selected="true">Create account</button>
                    <button type="button" class="account-tab" id="tab-login" role="tab" aria-selected="false">Log in</button>
                </div>
                <div id="register-pane" class="account-pane">
                    <h3>Create your Drypp account</h3>
                    <p class="muted">Your cart will be kept while you register.</p>
                    <form method="POST" action="{{ route('register') }}" id="register-form">
                        @csrf
                        <input type="hidden" name="pending_domain" class="js-pending-domain">
                        <input type="hidden" name="pending_hosting_package_id" class="js-pending-hosting-package">
                        <label for="register-name">Full name</label>
                        <input class="form-control" id="register-name" name="name" autocomplete="name" required>
                        <label for="register-email">Email address</label>
                        <input class="form-control" id="register-email" name="email" type="email" autocomplete="email" required>
                        <label for="register-password">Password</label>
                        <input class="form-control" id="register-password" name="password" type="password" autocomplete="new-password" required minlength="8">
                        <label for="register-confirm">Confirm password</label>
                        <input class="form-control" id="register-confirm" name="password_confirmation" type="password" autocomplete="new-password" required minlength="8">
                        <button class="btn btn-primary account-submit" type="submit">Create account & continue</button>
                    </form>
                </div>
                <div id="login-pane" class="account-pane" hidden>
                    <h3>Welcome back</h3>
                    <p class="muted">Log in to continue with your order.</p>
                    <form method="POST" action="{{ route('login') }}" id="login-form">
                        @csrf
                        <input type="hidden" name="pending_domain" class="js-pending-domain">
                        <input type="hidden" name="pending_hosting_package_id" class="js-pending-hosting-package">
                        <label for="login-email">Email address</label>
                        <input class="form-control" id="login-email" name="email" type="email" autocomplete="username" required>
                        <label for="login-password">Password</label>
                        <input class="form-control" id="login-password" name="password" type="password" autocomplete="current-password" required>
                        <label class="remember-row"><input type="checkbox" name="remember" value="1"> Remember me</label>
                        <button class="btn btn-primary account-submit" type="submit">Log in & continue</button>
                    </form>
                </div>
                <p class="muted legal-note">By continuing, you agree to Drypp’s terms and confirm your order details.</p>
            </aside>
        </div>
    </section>
</main>

<style>
.checkout-steps{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:28px}
.checkout-steps .step{border:1px solid var(--border);border-radius:14px;padding:13px 16px;background:rgba(255,255,255,.025);display:flex;flex-direction:column;gap:4px}
.checkout-steps .step.active{border-color:var(--blue)}
.checkout-steps small,.small-note{font-size:12px}.domain-search-form{display:flex;gap:10px;margin:22px 0}.domain-search-form .form-control{flex:1;min-width:0}
.section-heading{padding:18px 22px;border-bottom:1px solid var(--border)}.section-heading p{margin:6px 0 0;font-size:13px}
.domain-row{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;padding:18px 22px;border-bottom:1px solid var(--border)}
.domain-name{font-size:18px}.domain-state{font-size:13px;margin:5px 0}.available{color:#22c55e;font-weight:700}.taken{color:#f87171;font-weight:700}
.domain-actions{display:flex;align-items:center;gap:14px;flex-wrap:wrap}.domain-price{font-size:19px}.domain-price small{font-size:12px;font-weight:500}
.flow-panel{margin-top:26px;scroll-margin-top:22px}.panel-heading{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap}.panel-heading h2{margin:5px 0}.panel-heading p{margin:5px 0}
.hosting-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-top:20px}.hosting-option{border:1px solid var(--border);border-radius:14px;padding:18px;display:flex;flex-direction:column;gap:10px}.hosting-option h3,.hosting-option p{margin:0}.hosting-option-popular{border:2px solid var(--blue);position:relative;box-shadow:0 8px 28px rgba(55,95,255,.16);padding-top:28px}.popular-badge{position:absolute;top:0;right:0;background:var(--blue);color:#fff;font-size:10px;font-weight:800;letter-spacing:.06em;padding:6px 10px;border-radius:0 12px 0 10px}.hosting-price{font-size:23px;font-weight:800}.hosting-price small{font-size:12px;font-weight:500}.hosting-option ul{padding-left:18px;line-height:1.8;font-size:13px;flex:1}
.checkout-grid{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(300px,.8fr);gap:24px;margin-top:18px;align-items:start}.cart-column{border:1px solid var(--border);border-radius:14px;padding:18px}
.cart-line{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;padding:14px 0;border-bottom:1px solid var(--border)}.cart-line:first-child{padding-top:0}.cart-line strong{overflow-wrap:anywhere}.cart-line .muted{font-size:12px;margin-top:4px}.remove-item{display:block;margin-top:8px;padding:4px 8px;font-size:12px}
.cart-total{display:flex;justify-content:space-between;align-items:center;gap:12px;padding-top:18px;font-size:16px}.cart-total strong{font-size:23px}
.account-box{border:1px solid var(--border);border-radius:14px;padding:0;overflow:hidden;background:rgba(255,255,255,.025)}.account-tabs{display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid var(--border)}.account-tab{border:0;border-bottom:3px solid transparent;padding:15px 10px;background:transparent;color:var(--text,#fff);font-weight:700;cursor:pointer}.account-tab.active{border-bottom-color:var(--blue);background:rgba(255,255,255,.04)}
.account-pane{padding:20px}.account-pane h3{margin:0 0 5px}.account-pane p{font-size:13px;margin:0 0 16px}.account-pane form{display:flex;flex-direction:column;gap:8px}.account-pane label{font-size:13px;font-weight:600;margin-top:5px}.account-pane .form-control{width:100%;min-width:0}.account-submit{width:100%;margin-top:12px;color:#fff!important;opacity:1!important;font-weight:700}
.remember-row{display:flex;align-items:center;gap:8px;font-weight:400!important}.legal-note{font-size:11px;padding:0 20px 18px;margin:0}.empty-results{padding:24px}
[hidden]{display:none!important}
@media(max-width:760px){.checkout-grid{grid-template-columns:1fr}.checkout-steps{gap:7px}.checkout-steps .step{padding:10px;font-size:13px}.domain-actions{width:100%;justify-content:space-between}.domain-search-form{flex-direction:column}.domain-search-form button{width:100%}}
</style>
<script>
(function(){
 let selectedDomain=null, selectedHosting=null;
 const domainPanel=document.getElementById('domain-panel'), hostingPanel=document.getElementById('hosting-panel'), cartPanel=document.getElementById('cart-panel');
 const summary=document.getElementById('chosen-domain-summary'), lines=document.getElementById('cart-lines'), total=document.getElementById('cart-total');
 const money=n=>'$'+Number(n||0).toFixed(2);
 const safe=s=>String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
 function renderCart(){
   if(!selectedDomain)return;
   let sum=Number(selectedDomain.price||0);
   const rows=[`<div class="cart-line"><div><strong>${safe(selectedDomain.domain)}</strong><div class="muted">Domain registration · first year</div><button type="button" class="btn btn-light remove-item" id="remove-domain">Remove</button></div><strong>${money(selectedDomain.price)}</strong></div>`];
   if(selectedHosting){sum+=Number(selectedHosting.price||0);rows.push(`<div class="cart-line"><div><strong>${safe(selectedHosting.name)}</strong><div class="muted">Hosting · ${safe(selectedHosting.cycle)}</div><button type="button" class="btn btn-light remove-item" id="remove-hosting">Remove</button></div><strong>${money(selectedHosting.price)}</strong></div>`);}
   lines.innerHTML=rows.join('');total.textContent=money(sum);
   document.getElementById('remove-domain').addEventListener('click',()=>{selectedDomain=null;selectedHosting=null;cartPanel.hidden=true;hostingPanel.hidden=true;domainPanel.hidden=false;document.getElementById('step-hosting').classList.remove('active');document.getElementById('step-cart').classList.remove('active');});
   document.getElementById('remove-hosting')?.addEventListener('click',()=>{selectedHosting=null;renderCart();});
 }
 function showCart(){
   hostingPanel.hidden=true;
   cartPanel.hidden=false;
   document.getElementById('step-cart').classList.add('active');
   renderCart();
   cartPanel.scrollIntoView({behavior:'smooth',block:'start'});
 }
 document.querySelectorAll('.js-domain-select').forEach(btn=>btn.addEventListener('click',()=>{
   selectedDomain={domain:btn.dataset.domain,price:Number(btn.dataset.price),renewal:Number(btn.dataset.renewal)};
   selectedHosting=null;summary.textContent='Selected domain: '+selectedDomain.domain+' · '+money(selectedDomain.price)+' first year';
   domainPanel.hidden=true;hostingPanel.hidden=false;cartPanel.hidden=true;document.getElementById('step-hosting').classList.add('active');hostingPanel.scrollIntoView({behavior:'smooth',block:'start'});
 }));
 document.getElementById('edit-domain').addEventListener('click',()=>{hostingPanel.hidden=true;cartPanel.hidden=true;domainPanel.hidden=false;document.getElementById('step-hosting').classList.remove('active');document.getElementById('step-cart').classList.remove('active');window.scrollTo({top:0,behavior:'smooth'});});
 document.getElementById('back-hosting').addEventListener('click',()=>{cartPanel.hidden=true;domainPanel.hidden=true;hostingPanel.hidden=false;document.getElementById('step-cart').classList.remove('active');hostingPanel.scrollIntoView({behavior:'smooth',block:'start'});});
 document.querySelectorAll('.js-hosting-select').forEach(btn=>btn.addEventListener('click',()=>{
   selectedHosting={id:btn.dataset.package,name:btn.dataset.name,price:Number(btn.dataset.price),cycle:btn.dataset.cycle};
   showCart();
 }));
 document.getElementById('domain-only').addEventListener('click',()=>{selectedHosting=null;showCart();});
 document.getElementById('tab-register').addEventListener('click',()=>setTab('register'));
 document.getElementById('tab-login').addEventListener('click',()=>setTab('login'));
 function setTab(which){
   const register=which==='register';
   document.getElementById('register-pane').hidden=!register;
   document.getElementById('login-pane').hidden=register;
   document.getElementById('tab-register').classList.toggle('active',register);
   document.getElementById('tab-login').classList.toggle('active',!register);
   document.getElementById('tab-register').setAttribute('aria-selected',String(register));
   document.getElementById('tab-login').setAttribute('aria-selected',String(!register));
 }
 // Persist the cart before auth POST so a failed login can restore it.
 function persistCart(){
   if(selectedDomain) sessionStorage.setItem('drypp_pending_cart', JSON.stringify({domain:selectedDomain, hosting:selectedHosting}));
 }
 function attachPendingCart(form){
   if(!form) return;
   if(!selectedDomain){
     const saved=sessionStorage.getItem('drypp_pending_cart');
     if(saved){try{const data=JSON.parse(saved);selectedDomain=data.domain;selectedHosting=data.hosting||null;}catch(e){}}
   }
   if(!selectedDomain) return;
   const domainInput=form.querySelector('.js-pending-domain');
   const packageInput=form.querySelector('.js-pending-hosting-package');
   if(domainInput) domainInput.value=selectedDomain.domain;
   if(packageInput) packageInput.value=selectedHosting ? selectedHosting.id : '';
   persistCart();
 }
 document.getElementById('register-form')?.addEventListener('submit',function(){attachPendingCart(this);});
 document.getElementById('login-form')?.addEventListener('submit',function(){attachPendingCart(this);});

 // Restore the cart after Laravel redirects back on an invalid password.
 const saved=sessionStorage.getItem('drypp_pending_cart');
 if(saved){
   try{
     const data=JSON.parse(saved);
     if(data.domain && data.domain.domain){
       selectedDomain=data.domain;
       selectedHosting=data.hosting||null;
       summary.textContent='Selected domain: '+selectedDomain.domain+' · '+money(selectedDomain.price)+' first year';
       domainPanel.hidden=true;
       hostingPanel.hidden=false;
       showCart();
       if(@json($errors->any()) && @json(old('email') && !old('name'))) setTab('login');
     }
   }catch(e){sessionStorage.removeItem('drypp_pending_cart');}
 }
})();
</script>
@endsection
