(() => {
  const body = document.body;

  function setupLoaderAndPerformance(){
    const preloader = document.getElementById('sitePreloader');
    const isSmall = window.innerWidth < 820;
    const lowMemory = navigator.deviceMemory && navigator.deviceMemory <= 4;
    const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
    if(isSmall || lowMemory || reduced){ body.classList.add('performance-lite'); }

    let frames = 0;
    let start = performance.now();
    function measure(now){
      frames++;
      if(now - start < 700){ requestAnimationFrame(measure); return; }
      const fps = frames * 1000 / (now - start);
      body.dataset.fps = String(Math.round(fps));
      if(fps < 46) body.classList.add('performance-lite');
    }
    requestAnimationFrame(measure);

    const hideLoader = () => {
      if(!preloader) return;
      preloader.classList.add('loaded');
      setTimeout(() => { preloader.hidden = true; }, 520);
    };
    window.addEventListener('load', () => setTimeout(hideLoader, 450), {once:true});
    setTimeout(hideLoader, 2600);
  }
  setupLoaderAndPerformance();
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
  const products = (window.APP && window.APP.products) || [];
  let activeProduct = products[0] || null;

  function safeText(value){ return String(value ?? '').replace(/[<>]/g, ''); }

  function applyTheme(theme){
    body.dataset.theme = theme;
    localStorage.setItem('atoz_theme', theme);
    const btn = $('#themeToggle span') || $('#themeToggle');
    if(btn) btn.textContent = theme === 'dark' ? '☀' : '☾';
  }

  function applyLang(lang){
    body.dataset.lang = lang;
    document.documentElement.lang = lang === 'bn' ? 'bn' : 'en';
    localStorage.setItem('atoz_lang', lang);
    $$('[data-en][data-bn]').forEach(el => {
      if(el.classList.contains('blog-content-v33')) return;
      const value = el.dataset[lang] || el.dataset.en || el.textContent;
      if(/<br\s*\/?\s*>/i.test(value)){ el.innerHTML = value; }
      else { el.textContent = value; }
    });
    $$('[data-placeholder-en][data-placeholder-bn]').forEach(el => {
      const value = lang === 'bn' ? el.dataset.placeholderBn : el.dataset.placeholderEn;
      if(value) el.setAttribute('placeholder', value);
    });
    $$('.blog-content-v33[data-en][data-bn]').forEach(el => {
      const value = el.dataset[lang] || el.dataset.en || '';
      const blocks = String(value).trim().split(/\n\s*\n/).filter(Boolean);
      el.innerHTML = blocks.map(block => {
        if(/^[-*]\s+/m.test(block)){
          return '<ul>' + block.split(/\r?\n/).map(line => line.replace(/^[-*]\s+/, '').trim()).filter(Boolean).map(line => '<li>' + safeText(line) + '</li>').join('') + '</ul>';
        }
        return '<p>' + safeText(block).replace(/\n/g, '<br>') + '</p>';
      }).join('');
    });
    const btn = $('#langToggle span') || $('#langToggle');
    if(btn) btn.textContent = lang === 'en' ? 'BN' : 'EN';
    syncActiveProduct(activeProduct, false);
  }

  applyTheme(localStorage.getItem('atoz_theme') || 'dark');
  applyLang(localStorage.getItem('atoz_lang') || 'en');

  $('#themeToggle')?.addEventListener('click', () => applyTheme(body.dataset.theme === 'dark' ? 'light' : 'dark'));
  $('#langToggle')?.addEventListener('click', () => applyLang(body.dataset.lang === 'en' ? 'bn' : 'en'));
  const mobilePanel = $('#mobilePanel');
  const menuToggle = $('#menuToggle');
  const menuClose = $('#menuClose');
  function setMobileMenu(open){
    if(!mobilePanel) return;
    mobilePanel.classList.toggle('open', open);
    mobilePanel.setAttribute('aria-hidden', open ? 'false' : 'true');
    menuToggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
    body.classList.toggle('menu-open', open);
  }
  menuToggle?.setAttribute('aria-controls', 'mobilePanel');
  menuToggle?.setAttribute('aria-expanded', 'false');
  menuToggle?.addEventListener('click', () => setMobileMenu(!mobilePanel?.classList.contains('open')));
  menuClose?.addEventListener('click', () => setMobileMenu(false));
  $$('#mobilePanel a').forEach(a => a.addEventListener('click', () => setMobileMenu(false)));
  const backToTop = $('#backToTop');
  if(backToTop){
    const toggleTop = () => backToTop.classList.toggle('show', window.scrollY > 520);
    addEventListener('scroll', toggleTop, {passive:true});
    toggleTop();
    backToTop.addEventListener('click', () => scrollTo({top:0, behavior:'smooth'}));
  }
  document.addEventListener('click', (e) => {
    const panel = $('#mobilePanel');
    const menu = $('#menuToggle');
    if(!panel || !panel.classList.contains('open')) return;
    if(panel.contains(e.target) || menu?.contains(e.target)) return;
    setMobileMenu(false);
  });
  document.addEventListener('keydown', (e) => {
    if(e.key === 'Escape') setMobileMenu(false);
  });

  const mobileLinks = $$('#mobilePanel a[href^="#"]');
  const mobileSections = mobileLinks
    .map(link => ({link, section: $(link.getAttribute('href'))}))
    .filter(item => item.section);
  function syncMobileActiveLink(){
    if(!mobileSections.length) return;
    const y = window.scrollY + 120;
    let current = mobileSections[0];
    mobileSections.forEach(item => {
      if(item.section.offsetTop <= y) current = item;
    });
    mobileLinks.forEach(link => link.classList.toggle('active', link === current.link));
  }
  addEventListener('scroll', syncMobileActiveLink, {passive:true});
  syncMobileActiveLink();

  function featureHTML(product){
    return (product?.features || []).map(f => `<span>${safeText(f)}</span>`).join('');
  }

  function syncActiveProduct(product, animate = true){
    if(!product) return;
    activeProduct = product;
    const lang = body.dataset.lang || 'en';
    const accent = product.accent_color || (product.brand === 'SIGNAL' ? '#29d366' : '#f7b733');
    const stage = $('#productStage'); if(stage) stage.style.setProperty('--accent', accent);
    const img = $('#stageProductImage'); const ref = $('#stageReflection');
    if(img){ img.src = product.image; img.alt = product[`name_${lang}`] || product.name_en; }
    if(ref) ref.src = product.image;
    const name = $('#stageName');
    if(name){
      name.textContent = product[`name_${lang}`] || product.name_en;
      name.dataset.en = product.name_en || '';
      name.dataset.bn = product.name_bn || product.name_en || '';
    }
    const badge = $('#stageBadge'); if(badge) badge.textContent = product.badge || product.brand;
    const desc = $('#experienceDesc');
    if(desc){
      desc.textContent = product[`short_description_${lang}`] || product.short_description_en || '';
      desc.dataset.en = product.short_description_en || '';
      desc.dataset.bn = product.short_description_bn || product.short_description_en || '';
    }
    const features = $('#experienceFeatures'); if(features) features.innerHTML = featureHTML(product);

    if(animate && window.gsap){
      gsap.fromTo('#stageProductImage', {y:28, opacity:.22, rotateY:-10, scale:.96}, {y:0, opacity:1, rotateY:0, scale:1, duration:.65, ease:'power3.out'});
      gsap.fromTo('.protection-ring', {scale:.82, opacity:.2}, {scale:1, opacity:1, duration:.65, ease:'power3.out'});
      gsap.fromTo('.experience-features span', {y:14, opacity:0}, {y:0, opacity:1, stagger:.055, duration:.36, ease:'power2.out'});
    }
  }
  syncActiveProduct(activeProduct, false);

  const productCards = $$('.product-card[data-product-id]');
  const autoDelay = 3500;
  let activeIndex = Math.max(0, productCards.findIndex(card => card.classList.contains('active')));
  let autoTimer = null;
  let resumeTimer = null;
  let productAutoPaused = false;

  function setActiveCard(card, animate = true){
    if(!card) return;
    productCards.forEach(c => {
      const isActive = c === card;
      c.classList.toggle('active', isActive);
      c.setAttribute('aria-selected', isActive ? 'true' : 'false');
      c.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });

    activeIndex = Math.max(0, productCards.indexOf(card));
    const id = Number(card.dataset.productId);
    const product = products.find(p => Number(p.id) === id) || products[activeIndex] || products[0];
    syncActiveProduct(product, animate);

    // Do not auto-scroll product cards during autoplay; it can fight mobile touch scrolling.
  }

  function selectProductByIndex(index, animate = true){
    if(!productCards.length) return;
    const nextIndex = (index + productCards.length) % productCards.length;
    setActiveCard(productCards[nextIndex], animate);
  }

  function stopProductAuto(){
    if(autoTimer){ clearTimeout(autoTimer); autoTimer = null; }
  }

  function scheduleProductAuto(delay = autoDelay){
    stopProductAuto();
    if(productAutoPaused || productCards.length < 2 || matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    autoTimer = setTimeout(() => {
      selectProductByIndex(activeIndex + 1, true);
      scheduleProductAuto(autoDelay);
    }, delay);
  }

  function startProductAuto(){
    if(resumeTimer){ clearTimeout(resumeTimer); resumeTimer = null; }
    scheduleProductAuto(autoDelay);
  }

  function pauseProductAuto(nextAfter = 0){
    stopProductAuto();
    if(resumeTimer){ clearTimeout(resumeTimer); resumeTimer = null; }
    if(nextAfter > 0){
      resumeTimer = setTimeout(() => {
        if(productAutoPaused) return;
        selectProductByIndex(activeIndex + 1, true);
        scheduleProductAuto(autoDelay);
      }, nextAfter);
    }
  }

  const productModal = $('#productModal');
  let modalProduct = null;

  function productFromCard(card){
    const id = Number(card?.dataset?.productId);
    return products.find(p => Number(p.id) === id) || products[Math.max(0, Number(card?.dataset?.index || 0))] || activeProduct || products[0];
  }

  function fillProductModal(product){
    if(!productModal || !product) return;
    modalProduct = product;
    const lang = body.dataset.lang || 'en';
    const accent = product.accent_color || (product.brand === 'SIGNAL' ? '#29d366' : '#f7b733');
    productModal.style.setProperty('--modal-accent', accent);
    const title = product[`name_${lang}`] || product.name_en || '';
    const desc = product[`short_description_${lang}`] || product.short_description_en || '';
    const features = (product.features || []).map(f => `<span>${safeText(f)}</span>`).join('');

    const img = $('#modalProductImage');
    if(img){ img.src = product.image || ''; img.alt = title; }
    const badge = $('#modalProductBadge');
    if(badge) badge.textContent = `${product.brand || ''} • ${product.badge || product.category || 'Premium'}`;
    const name = $('#modalProductName');
    if(name) name.textContent = title;
    const text = $('#modalProductDesc');
    if(text) text.textContent = desc;
    const hours = $('#modalProductHours');
    if(hours) hours.textContent = `${product.protection_hours || ''} Protection`.trim();
    const featureWrap = $('#modalProductFeatures');
    if(featureWrap) featureWrap.innerHTML = features || '<span>Premium formula</span><span>Long lasting protection</span>';
    const page = $('#modalProductPage');
    if(page){
      var productBase = (window.ATOZ_ROUTES && window.ATOZ_ROUTES.productBase) ? window.ATOZ_ROUTES.productBase : 'product-details.php?slug=';
      page.href = productBase.indexOf('product-details.php') !== -1 ? productBase + encodeURIComponent(product.slug || '') : productBase + encodeURIComponent(product.slug || '');
    }
    const enquiry = $('#modalProductEnquiry');
    if(enquiry){
      enquiry.href = '#distributorForm';
      enquiry.onclick = () => { prepareProductLead(product); closeProductModal(); };
    }
    const catalogue = $('#modalProductCatalogue');
    if(catalogue){
      var catalogueBase = (window.ATOZ_ROUTES && window.ATOZ_ROUTES.catalogue) ? window.ATOZ_ROUTES.catalogue : 'catalogue-download.php';
      catalogue.href = catalogueBase + '?source=Product%20Modal&product=' + encodeURIComponent(product.slug || '');
    }
  }

  function openProductModal(product){
    if(!productModal || !product) return;
    fillProductModal(product);
    productAutoPaused = true;
    pauseProductAuto();
    productModal.hidden = false;
    productModal.setAttribute('aria-hidden', 'false');
    body.classList.add('modal-open');
    requestAnimationFrame(() => {
      productModal.classList.add('open');
      if(window.gsap){
        gsap.fromTo('.product-modal-dialog', {y:28, scale:.96, opacity:0}, {y:0, scale:1, opacity:1, duration:.36, ease:'power3.out'});
        gsap.fromTo('#modalProductImage', {y:18, rotateY:-8, opacity:.45}, {y:0, rotateY:0, opacity:1, duration:.52, ease:'power3.out'});
      }
    });
    setTimeout(() => $('#productModalClose')?.focus({preventScroll:true}), 80);
  }

  function closeProductModal(){
    if(!productModal || productModal.hidden) return;
    productModal.classList.remove('open');
    productModal.setAttribute('aria-hidden', 'true');
    body.classList.remove('modal-open');
    setTimeout(() => { productModal.hidden = true; }, 260);
    productAutoPaused = false;
    pauseProductAuto(1800);
  }

  $$('[data-modal-close]').forEach(btn => btn.addEventListener('click', closeProductModal));
  document.addEventListener('keydown', e => {
    if(e.key === 'Escape' && productModal && !productModal.hidden) closeProductModal();
  });

  function ensureHidden(form, name, value){
    if(!form) return;
    let input = form.querySelector(`input[name="${name}"]`);
    if(!input){
      input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      form.appendChild(input);
    }
    const safeValue = value || '';
    input.value = safeValue;
    input.defaultValue = safeValue;
  }

  function prepareProductLead(product){
    const form = $('#distributorForm');
    if(!form || !product) return;
    ensureHidden(form, 'enquiry_type', 'Product Enquiry');
    ensureHidden(form, 'product_slug', product.slug || '');
    ensureHidden(form, 'product_name', product.name_en || '');
    const brandSelect = form.querySelector('select[name="interested_brand"]');
    if(brandSelect && product.brand){ brandSelect.value = product.brand; }
    const msg = form.querySelector('[name="message"]');
    if(msg && !msg.value.trim()){ msg.value = `I want to know more about ${product.name_en || 'this product'}.`; }
    setTimeout(() => form.scrollIntoView({behavior:'smooth', block:'start'}), 30);
  }

  productCards.forEach((card, index) => {
    card.setAttribute('role', 'button');
    card.setAttribute('aria-selected', card.classList.contains('active') ? 'true' : 'false');
    card.setAttribute('aria-pressed', card.classList.contains('active') ? 'true' : 'false');
    card.dataset.index = String(index);

    const activate = () => {
      setActiveCard(card, true);
      pauseProductAuto(3500);
    };

    const detailsBtn = $('.mini-btn', card);
    if(detailsBtn){
      detailsBtn.addEventListener('click', e => {
        e.preventDefault();
        e.stopPropagation();
        const product = productFromCard(card);
        setActiveCard(card, true);
        openProductModal(product);
      });
    }

    card.addEventListener('click', e => {
      if(e.target.closest('a')) return;
      activate();
    });
    card.addEventListener('keydown', e => {
      if(e.key === 'Enter' || e.key === ' '){ e.preventDefault(); activate(); }
      if(e.key === 'ArrowRight'){ e.preventDefault(); selectProductByIndex(activeIndex + 1, true); pauseProductAuto(3500); }
      if(e.key === 'ArrowLeft'){ e.preventDefault(); selectProductByIndex(activeIndex - 1, true); pauseProductAuto(3500); }
    });
  });

  document.addEventListener('visibilitychange', () => {
    if(document.hidden) pauseProductAuto();
    else startProductAuto();
  });

  // Start after first paint so the initial product is visible, then rotate one by one.
  setTimeout(() => {
    selectProductByIndex(activeIndex, false);
    startProductAuto();
  }, 300);

  function attachTilt(el, max = 10){
    if(!el || matchMedia('(pointer: coarse)').matches) return;
    const original = getComputedStyle(el).transform === 'none' ? '' : getComputedStyle(el).transform;
    el.addEventListener('pointermove', e => {
      const r = el.getBoundingClientRect();
      const x = (e.clientX - r.left) / r.width - .5;
      const y = (e.clientY - r.top) / r.height - .5;
      el.style.transform = `${original} rotateY(${x * max}deg) rotateX(${-y * max * .7}deg) translateY(-4px)`;
    });
    el.addEventListener('pointerleave', () => { el.style.transform = original; });
  }
  $$('.tilt-product').forEach(el => attachTilt(el, el.classList.contains('stage-product') ? 9 : 6));

  function initReveal(){
    const revealEls = $$('.reveal');
    const revealOne = el => {
      if(window.gsap && !body.classList.contains('performance-lite')){ gsap.to(el, {opacity:1, y:0, duration:.52, ease:'power2.out'}); }
      else { el.style.opacity = 1; el.style.transform = 'translateY(0)'; }
    };
    const io = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if(entry.isIntersecting){ revealOne(entry.target); io.unobserve(entry.target); }
      });
    }, {threshold:.15, rootMargin:'0px 0px -50px 0px'});
    revealEls.forEach(el => io.observe(el));
    setTimeout(() => $$('.hero-section .reveal').forEach(revealOne), 250);
  }
  setTimeout(initReveal, 250);

  const navLinks = $$('.desktop-nav a[href^="#"]');
  const sections = navLinks.map(a => $(a.getAttribute('href'))).filter(Boolean);
  const navObserver = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if(!entry.isIntersecting) return;
      navLinks.forEach(a => a.classList.toggle('active', a.getAttribute('href') === `#${entry.target.id}`));
    });
  }, {rootMargin:'-45% 0px -50% 0px'});
  sections.forEach(s => navObserver.observe(s));

  const counters = $$('.counter');
  const counterObserver = new IntersectionObserver(entries => entries.forEach(entry => {
    if(!entry.isIntersecting) return;
    const el = entry.target;
    const target = Number(el.dataset.target || 0);
    const duration = 1350;
    const t0 = performance.now();
    function tick(now){
      const p = Math.min((now - t0) / duration, 1);
      const eased = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.round(target * eased).toLocaleString();
      if(p < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
    counterObserver.unobserve(el);
  }), {threshold:.42});
  counters.forEach(c => counterObserver.observe(c));

  const leadForms = Array.from(new Set($$('.lead-enquiry-form, #distributorForm')));
  leadForms.forEach(form => {
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const status = $('.form-status', form) || $('#formStatus');
      const successActions = $('.form-success-actions-v39', form) || $('#formSuccessActions');
      const whatsAppLink = $('.form-whatsapp-link', form) || $('#formWhatsAppLink');
      if(successActions) successActions.hidden = true;
      if(status) status.textContent = 'Submitting...';
      const submitButton = form.querySelector('button[type="submit"]');
      try{
        if(submitButton) submitButton.disabled = true;
        const res = await fetch(form.action, {method:'POST', body:new FormData(form), headers:{'X-CSRF-Token':window.APP?.csrf || ''}});
        const data = await res.json();
        if(status){
          status.textContent = data.message || (data.ok ? 'Submitted successfully.' : 'Submission failed.');
          status.classList.toggle('is-success', !!data.ok);
          status.classList.toggle('is-error', !data.ok);
        }
        if(data.ok){
          const preservedProduct = form.querySelector('input[name="product_slug"]')?.value || '';
          form.reset();
          if(preservedProduct){
            ensureHidden(form, 'product_slug', preservedProduct);
            ensureHidden(form, 'enquiry_type', data.enquiry_type || 'Product Enquiry');
          }
          if(data.whatsapp_url && successActions && whatsAppLink){
            whatsAppLink.href = data.whatsapp_url;
            successActions.hidden = false;
          }
          if(window.fbq){ try{ fbq('trackCustom', data.enquiry_type === 'Product Enquiry' ? 'ProductLeadSubmitted' : 'DistributorLeadSubmitted'); }catch(_e){} }
          if(window.gtag){ try{ gtag('event', data.enquiry_type === 'Product Enquiry' ? 'product_lead_submitted' : 'distributor_lead_submitted'); }catch(_e){} }
        }
      }catch(err){
        if(status){
          status.textContent = 'Submission failed. Check database configuration.';
          status.classList.add('is-error');
          status.classList.remove('is-success');
        }
      }finally{
        if(submitButton) submitButton.disabled = false;
      }
    });
  });

  $$('[data-catalogue-download]').forEach(link => {
    link.addEventListener('click', () => {
      const label = link.dataset.productName || 'Global Catalogue';
      if(window.fbq){ try{ fbq('trackCustom', 'CatalogueDownloadClicked', {product: label}); }catch(_e){} }
      if(window.gtag){ try{ gtag('event', 'catalogue_download_click', {product: label}); }catch(_e){} }
    });
  });

  const lazyMap = $('[data-map-lazy]');
  if(lazyMap){
    lazyMap.addEventListener('click', () => {
      const card = lazyMap.closest('.office-map-card');
      const iframe = card ? card.querySelector('iframe[data-src]') : null;
      if(iframe && !iframe.src){ iframe.src = iframe.dataset.src; }
      if(card) card.classList.add('is-map-loaded-v39');
    }, {once:true});
  }

  function initParticles(){
    const canvas = $('#particleCanvas');
    if(!canvas || !window.THREE || body.classList.contains('performance-lite') || innerWidth < 900 || matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(75, innerWidth / innerHeight, .1, 1000);
    const renderer = new THREE.WebGLRenderer({canvas, alpha:true, antialias:true});
    renderer.setSize(innerWidth, innerHeight);
    renderer.setPixelRatio(Math.min(devicePixelRatio, 1.45));
    camera.position.z = 5;
    const count = innerWidth < 1200 ? 60 : 120;
    const geometry = new THREE.BufferGeometry();
    const pos = new Float32Array(count * 3);
    for(let i=0; i<count*3; i+=3){
      pos[i] = (Math.random() - .5) * 12;
      pos[i+1] = (Math.random() - .5) * 8;
      pos[i+2] = (Math.random() - .5) * 8;
    }
    geometry.setAttribute('position', new THREE.BufferAttribute(pos, 3));
    const material = new THREE.PointsMaterial({size:.025, color:0xf7b733, transparent:true, opacity:.56});
    const points = new THREE.Points(geometry, material);
    scene.add(points);
    function animate(){
      points.rotation.y += .0009;
      points.rotation.x += .00035;
      renderer.render(scene, camera);
      requestAnimationFrame(animate);
    }
    animate();
    addEventListener('resize', () => {
      camera.aspect = innerWidth / innerHeight;
      camera.updateProjectionMatrix();
      renderer.setSize(innerWidth, innerHeight);
    }, {passive:true});
  }
  setTimeout(initParticles, 700);
})();

// v7 hero: premium parallax and entrance polish
(function(){
  function qs(sel, root){ return (root || document).querySelector(sel); }
  function qsa(sel, root){ return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }
  document.addEventListener('DOMContentLoaded', function(){
    var hero = qs('#heroVisualV7');
    if(hero && !document.body.classList.contains('performance-lite') && !matchMedia('(pointer: coarse)').matches){
      var main = qs('[data-parallax="main"]', hero);
      var side = qs('[data-parallax="side"]', hero);
      var coil = qs('[data-parallax="coil"]', hero);
      var smoke = qsa('.hero-v7-smoke', hero);
      hero.addEventListener('pointermove', function(e){
        var r = hero.getBoundingClientRect();
        var x = (e.clientX - r.left) / r.width - 0.5;
        var y = (e.clientY - r.top) / r.height - 0.5;
        if(main) main.style.transform = 'rotateY(' + (-10 + x * 5).toFixed(2) + 'deg) rotateX(' + (2 - y * 3).toFixed(2) + 'deg) rotateZ(' + (.8 + x * 1.5).toFixed(2) + 'deg) translate3d(' + (x * 14).toFixed(1) + 'px,' + (y * 9).toFixed(1) + 'px,46px)';
        if(side) side.style.transform = 'rotateY(' + (13 + x * 6).toFixed(2) + 'deg) rotateX(' + (2 - y * 4).toFixed(2) + 'deg) rotateZ(' + (-5 + x * 2).toFixed(2) + 'deg) translate3d(' + (x * 20).toFixed(1) + 'px,' + (y * 12).toFixed(1) + 'px,24px)';
        if(coil) coil.style.transform = 'perspective(1100px) rotateX(0deg) translate3d(' + (x * 10).toFixed(1) + 'px,' + (y * 5).toFixed(1) + 'px,58px)';
        smoke.forEach(function(s, i){ s.style.transform = 'translate3d(' + (x * (8 + i * 4)).toFixed(1) + 'px,' + (y * (6 + i * 2)).toFixed(1) + 'px,0)'; });
      });
      hero.addEventListener('pointerleave', function(){
        if(main) main.style.transform = '';
        if(side) side.style.transform = '';
        if(coil) coil.style.transform = '';
        smoke.forEach(function(s){ s.style.transform = ''; });
      });
    }

    if(window.gsap && !document.body.classList.contains('performance-lite')){
      gsap.from('.hero-v7-copy > *', {y:28, opacity:0, duration:.8, stagger:.1, ease:'power3.out', delay:.18});
      gsap.from('.hero-v7-pack-main', {x:54, y:18, scale:.94, opacity:0, duration:1.05, ease:'power3.out', delay:.28});
      gsap.from('.hero-v7-pack-side', {x:86, y:22, scale:.91, opacity:0, duration:1.05, ease:'power3.out', delay:.40});
      gsap.from('.hero-v7-coil-wrap', {y:38, scale:.92, opacity:0, duration:.95, ease:'power3.out', delay:.55});
      gsap.from('.hero-v7-feature', {y:16, opacity:0, duration:.55, stagger:.08, ease:'power2.out', delay:.72});
    }
  });
})();

// v30: lightweight archive gallery lightbox
(function(){
  document.addEventListener('DOMContentLoaded', function(){
    var links = Array.prototype.slice.call(document.querySelectorAll('[data-lightbox-gallery]'));
    if(!links.length) return;
    var modal = document.createElement('div');
    modal.className = 'gallery-lightbox-v30';
    modal.hidden = true;
    modal.innerHTML = '<button type="button" class="gallery-lightbox-close-v30" aria-label="Close photo">×</button><button type="button" class="gallery-lightbox-prev-v30" aria-label="Previous photo">‹</button><img alt=""><button type="button" class="gallery-lightbox-next-v30" aria-label="Next photo">›</button><p></p>';
    document.body.appendChild(modal);
    var img = modal.querySelector('img');
    var title = modal.querySelector('p');
    var index = 0;
    function show(i){
      index = (i + links.length) % links.length;
      var link = links[index];
      img.src = link.getAttribute('href');
      img.alt = link.getAttribute('data-lightbox-title') || link.querySelector('img')?.alt || 'Archive photo';
      title.textContent = link.getAttribute('data-lightbox-title') || img.alt;
      modal.hidden = false;
      document.body.classList.add('modal-open');
      requestAnimationFrame(function(){ modal.classList.add('open'); });
    }
    function close(){
      modal.classList.remove('open');
      document.body.classList.remove('modal-open');
      setTimeout(function(){ modal.hidden = true; img.removeAttribute('src'); }, 220);
    }
    links.forEach(function(link, i){
      link.addEventListener('click', function(e){ e.preventDefault(); show(i); });
    });
    modal.querySelector('.gallery-lightbox-close-v30').addEventListener('click', close);
    modal.querySelector('.gallery-lightbox-prev-v30').addEventListener('click', function(){ show(index - 1); });
    modal.querySelector('.gallery-lightbox-next-v30').addEventListener('click', function(){ show(index + 1); });
    modal.addEventListener('click', function(e){ if(e.target === modal) close(); });
    document.addEventListener('keydown', function(e){
      if(modal.hidden) return;
      if(e.key === 'Escape') close();
      if(e.key === 'ArrowLeft') show(index - 1);
      if(e.key === 'ArrowRight') show(index + 1);
    });
  });
})();


// v33: blog share copy action
(function(){
  document.addEventListener('click', function(e){
    var btn = e.target.closest('[data-copy-link]');
    if(!btn) return;
    var link = btn.getAttribute('data-copy-link') || location.href;
    var done = function(){
      var old = btn.textContent;
      btn.textContent = 'Copied';
      document.querySelectorAll('.blog-content-v33').forEach(function(el){ el.classList.add('copied'); setTimeout(function(){ el.classList.remove('copied'); }, 1500); });
      setTimeout(function(){ btn.textContent = old || 'Copy Link'; }, 1500);
    };
    if(navigator.clipboard && navigator.clipboard.writeText){
      navigator.clipboard.writeText(link).then(done).catch(function(){ prompt('Copy this link:', link); });
    } else {
      prompt('Copy this link:', link);
    }
  });
})();
