<?php $__env->startSection('title','Drypp — Domains, Hosting, Email Marketing & Lead Generation'); ?>
<?php $__env->startSection('body'); ?>
<header class="site-header">
  <div class="wrap nav">
    <a class="brand" href="<?php echo e(route('home')); ?>"><span class="drop">◒</span>drypp<span style="color:var(--blue)">.</span></a>
    <nav class="navlinks"><a href="#domain-search-section">Domains & Hosting</a><a href="#plans">Hosting plans</a><a href="#why-drypp">Why Drypp</a><a href="#how">How it works</a></nav>
    <div class="actions"><label class="sr-only" for="display-currency">Display currency</label><select id="display-currency" aria-label="Display currency" class="header-select"><option value="USD">USD $</option><option value="NGN">NGN ₦</option><option value="GBP">GBP £</option><option value="EUR">EUR €</option></select><button id="theme-toggle" type="button" class="theme-toggle" aria-label="Toggle light and dark theme">☼ <span>Light mode</span></button><a class="btn btn-light" href="<?php echo e(route('login')); ?>">Log in</a><a class="btn btn-primary" href="<?php echo e(route('register')); ?>">Create account ↗</a></div>
  </div>
</header>

<section class="hero hero-rich">
  <div class="wrap hero-grid">
    <div class="hero-copy">
      <span class="eyebrow">✦ Your business growth toolkit</span>
      <h1>Build your online presence. <span>Grow with confidence.</span></h1>
      <p>Get your domain and hosting, connect with customers through email marketing, and discover new prospects — all from one Drypp account.</p>
      <div class="hero-actions"><a class="btn btn-primary" href="#domain-search-section">Find your domain ↗</a><a class="btn btn-light" href="#plans">View hosting plans</a></div>
    </div>
    <div class="hero-visual">
      <div class="visual-glow"></div>
      <div class="dash-art">
        <div class="dash-top"><strong>Your business workspace</strong><span class="live">● Connected services</span></div>
        <div class="workspace-grid">
          <div class="workspace-tile"><span class="tile-icon">⌘</span><small>Website</small><strong>Launch online</strong><em>Domain + hosting</em></div>
          <div class="workspace-tile"><span class="tile-icon">✉</span><small>Marketing</small><strong>Reach customers</strong><em>Campaigns + reports</em></div>
          <div class="workspace-tile"><span class="tile-icon">◎</span><small>Prospecting</small><strong>Find opportunities</strong><em>Targeted lead lists</em></div>
        </div>
        <div class="workspace-bottom"><div><span class="mini-label">YOUR BUSINESS, ONE PLACE</span><strong>Less juggling. More growing.</strong></div><span class="workspace-orb">↗</span></div>
      </div>
      <div class="floating-note"><span class="floating-check">✓</span><div><strong>One Drypp account</strong><small>Manage your services in one place</small></div></div>
    </div>
  </div>
</section>



<section class="domain-band domain-band-inline" id="domain-search-section"><div class="wrap">
  <div class="domain-inline-box">
    <form class="domain-search-form" id="domain-search-inline" action="<?php echo e(route('domains.search')); ?>" method="get">
      <label class="sr-only" for="domain-search-input">Domain name</label><span class="domain-prefix">www.</span><input id="domain-search-input" name="domain" type="text" placeholder="yourbusiness.com" autocomplete="off" required><button class="btn btn-primary" type="submit">Check availability ↗</button>
    </form>
    <div class="domain-exts"><span>.com</span><span>.net</span><span>.org</span><span>.co</span><span>.com.ng</span><span class="ext-note">More extensions available</span></div>
    <div class="domain-assurance"><span>Clear package limits</span><span>Secure account access</span><span>Manage services in one dashboard</span></div>
    <p class="domain-search-notice" id="domain-search-notice" hidden>Domain prices include Drypp’s markup. Availability is confirmed with the registrar before registration.</p>
  </div>
</div></section>

<section class="section pricing section-spacious" id="plans"><div class="wrap">
  <div class="section-head hosting-section-head"><h2>Hosting plans for every stage</h2><p>Three annual USD plans, with more capacity as your business grows. Currency conversions are indicative only.</p></div>
  <div class="cards"><?php $__empty_1 = true; $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i=>$package): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <article class="card pricecard <?php echo e($i===1 ? 'featured' : ''); ?>"><?php if($i===1): ?><span class="pill">POPULAR CHOICE</span><?php endif; ?><div class="plan-label">WEB HOSTING</div><h3><?php echo e($package->name); ?></h3><p><?php echo e($package->description ?: 'The essentials to get your business online.'); ?></p><div class="price"><span data-price-usd="<?php echo e((float) ($package->price_usd ?? 0)); ?>">$<?php echo e(number_format((float) ($package->price_usd ?? 0), 2)); ?></span> <small>/ <?php echo e($package->billing_cycle); ?></small></div><ul class="features"><li><?php echo e($package->disk_space_gb); ?> GB storage <small>(approx. <?php echo e(number_format($package->disk_space_gb * 1000)); ?> MB)</small></li><li><?php echo e(number_format($package->bandwidth_gb)); ?> GB bandwidth <small>(approx. <?php echo e(number_format($package->bandwidth_gb * 1000)); ?> MB)</small></li><li><?php echo e($package->email_accounts); ?> email accounts</li><li><?php echo e($package->addon_domains); ?> addon domains</li><li><?php echo e($package->databases); ?> MySQL databases</li><?php $__currentLoopData = ($package->features ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($feature); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul><a class="btn <?php echo e($i===1 ? 'btn-primary' : 'btn-light'); ?>" style="width:100%" href="<?php echo e(route('register')); ?>">Choose <?php echo e($package->name); ?> ↗</a><small class="plan-footnote">Domain registration and renewals may be priced separately.</small></article>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="card empty-plans"><div class="iconbox">▤</div><h3>Hosting plans are on the way</h3><p>Our hosting packages will appear here when an administrator activates them. In the meantime, create your account to explore Drypp.</p><a class="btn btn-primary" href="<?php echo e(route('register')); ?>">Create your account ↗</a></div>
  <?php endif; ?></div>
  <div class="plans-note"><span>✓</span><p><strong>Need help choosing?</strong> Compare the features above or contact the Drypp team before placing your order.</p><a href="<?php echo e(route('services')); ?>">View services →</a></div>
</div></section>

<section class="section" id="why-drypp"><div class="wrap why-grid">
  <div class="why-copy"><span class="eyebrow">Designed around your business</span><h2>Tools that move with you.</h2><p>Whether you're putting your first business online or improving the way you find customers, Drypp brings essential services into one clear workspace.</p><a class="btn btn-primary" href="<?php echo e(route('register')); ?>">Get started with Drypp ↗</a></div>
  <div class="benefit-grid">
    <article class="benefit"><span>01</span><h3>One account</h3><p>Access your purchased services and account details from one customer dashboard.</p></article>
    <article class="benefit"><span>02</span><h3>Know what you're buying</h3><p>Review package features, billing periods and applicable charges before checkout.</p></article>
    <article class="benefit"><span>03</span><h3>Room to expand</h3><p>Begin with hosting and add marketing or prospecting tools as your needs change.</p></article>
    <article class="benefit"><span>04</span><h3>Service visibility</h3><p>Keep track of orders, service status and next steps in your account.</p></article>
  </div>
</div></section>



<section class="section" id="how"><div class="wrap"><div class="section-head"><span class="eyebrow">A simpler way to get started</span><h2>From idea to online, in three steps.</h2><p>Choose a service, complete your order, then manage it through your Drypp account.</p></div><div class="steps">
  <article class="step"><div class="step-number">01</div><h3>Create your account</h3><p>Register once to access hosting, email marketing and lead-generation services.</p></article>
  <article class="step"><div class="step-number">02</div><h3>Choose what you need</h3><p>Review the available options, package limits and pricing before ordering.</p></article>
  <article class="step"><div class="step-number">03</div><h3>Manage and grow</h3><p>Return to your dashboard to view orders and access the services available to you.</p></article>
</div></div></section>

<section class="final-cta"><div class="wrap final-cta-inner"><div><span class="eyebrow">Your next step starts here</span><h2>Ready to build something online?</h2><p>Create your Drypp account and explore the services available for your business.</p></div><div class="hero-actions"><a class="btn btn-primary" href="<?php echo e(route('register')); ?>">Create a free account ↗</a><a class="btn btn-light" href="#plans">Explore services</a></div></div></section>
<script>
var inlineDomainForm=document.getElementById('domain-search-inline');

document.querySelectorAll('.domain-tab').forEach(function(tab){
  tab.addEventListener('click', function(){
    document.querySelectorAll('.domain-tab').forEach(function(t){t.classList.remove('active');t.setAttribute('aria-selected','false');});
    document.querySelectorAll('.domain-panel').forEach(function(panel){panel.classList.remove('active');panel.hidden=true;});
    tab.classList.add('active');tab.setAttribute('aria-selected','true');
    var panel=document.getElementById(tab.dataset.tab);panel.hidden=false;panel.classList.add('active');
  });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drypp\resources\views/public/home.blade.php ENDPATH**/ ?>