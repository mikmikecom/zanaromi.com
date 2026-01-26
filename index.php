<?php
// ---- 1) Připojení k DB
$pdo = new PDO(
  // uprav "app" na své jméno DB
  'mysql:host=localhost;dbname=shop-zanaromi;charset=utf8mb4', 
  // DB uživatel
  'shop-zanaromi', 
  // DB heslo                                           
  'l9s5dxyp2KEN',                                            
  [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]
);

// ---- 2) Seznam podporovaných jazyků
$supported = ['cs','de','gb','fr','hu','pl','ro','sk'];

// ---- 3) Určení jazyka
$lang = $_GET['lang'] ?? null;

// z cookie
if (!$lang && !empty($_COOKIE['lang']) && in_array($_COOKIE['lang'], $supported)) {
  $lang = $_COOKIE['lang'];
}

// z hlavičky prohlížeče
if (!$lang) {
  $pref = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2);
  $lang = in_array($pref, $supported) ? $pref : 'cs';
}

// fallback
if (!in_array($lang, $supported)) {
  $lang = 'cs';
}

// uložíme cookie
setcookie('lang', $lang, time()+60*60*24*365, '/');

// ---- 4) Načtení překladů z DB
$stmt = $pdo->prepare("SELECT `key`, `value` FROM hp_translations WHERE lang = ?");
$stmt->execute([$lang]);
$t = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

// ---- 5) Funkce pro překlad
function t($key, $default = '') {
  global $t;
  // pokud překlad existuje a není prázdný → vrať ho
  if (!empty($t[$key])) {
    return htmlspecialchars($t[$key], ENT_QUOTES, 'UTF-8');
  }
  // jinak fallback (pokud existuje), jinak samotný klíč
  return htmlspecialchars($default ?: $key, ENT_QUOTES, 'UTF-8');
}

// ---- 6) Cesta (pokud používáš routing)
$path = $_GET['path'] ?? '';
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" data-bs-theme="light" data-pwa="true">
  <head>
    <meta charset="utf-8">

    <!-- Viewport opraveno max z 1 na 5-->
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=5, viewport-fit=cover">

    <!-- SEO Meta Tags -->
    <title><?= t('meta.title', 'ZAN-AROMI | Flavors, Aromas & Essences for Spirits | Wine') ?></title>
    <meta name="description" content="<?= t('meta.desc', 'Flavours and Aromas for spirits flavoring, Alcohol Essences for flavoring Spirits, Liquor flavorings, Flavors for Winemaking, Wine Additives,Moonshiners') ?>">
    <meta name="keywords" content="<?= t('meta.keyw', 'home burning of spirits, home burning, flavoring of spirits, Flavours and Fragrances, spirits flavoring, Alcohol Essences, flavoring Spirits, Liqueur flavorings, Liquor flavorings, Flavors for Manufacturers, Winemaking, Wine Additives, Wine Flavorings') ?>">
    <meta name="author" content="ZAN-AROMI">


    <!-- Hreflang alternates -->
    <?php
    $hreflangBase = 'https://zanaromi.com';
    $hreflangPath = $path ? '/' . ltrim($path, '/') : '';
    $hreflangMap = [
      'cs' => 'cs-CZ',
      'de' => 'de-de',
      'gb' => 'en-GB',
      'fr' => 'fr-fr',
      'hu' => 'hu-hu',
      'pl' => 'pl-pl',
      'ro' => 'ro-ro',
      'sk' => 'sk-sk'
    ];
    foreach ($hreflangMap as $langCode => $hreflang) {
      $href = sprintf('%s/%s%s', $hreflangBase, $langCode, $hreflangPath);
      echo '<link rel="alternate" hreflang="' . $hreflang . '" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">' . PHP_EOL;
    }
    echo '<link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($hreflangBase . '/cs' . $hreflangPath, ENT_QUOTES, 'UTF-8') . '">' . PHP_EOL;
    ?>



    <!-- Webmanifest + Favicon / App icons -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="manifest" href="/x-manifest.json">
    <link rel="icon" type="image/png" href="/x-assets/app-icons/zan-logo-square-transp - 180x180.png" sizes="32x32">
    <link rel="apple-touch-icon" href="/x-assets/app-icons/zan-logo-square-transp - 180x180.png">

    <!-- Theme switcher (color modes) -->
    <script src="/x-assets/js/theme-switcher.js"></script>

    <!-- Preloaded local web font (Inter) -->
    <link rel="preload" href="/x-assets/fonts/inter-variable-latin.woff2" as="font" type="font/woff2" crossorigin>

    <!-- Font icons -->
    <link rel="preload" href="/x-assets/icons/cartzilla-icons.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/x-assets/icons/cartzilla-icons.min.css">

    <!-- Font Google -->
    <link href="https://fonts.googleapis.com/css2?family=Italianno&display=swap" rel="stylesheet">

    <!-- Vendor styles -->
    <link rel="stylesheet" href="/x-assets/vendor/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="/x-assets/vendor/simplebar/dist/simplebar.min.css">
    <link rel="stylesheet" href="/x-assets/vendor/glightbox/dist/css/glightbox.min.css">

    <!-- Bootstrap + Theme styles -->
    <link rel="preload" href="/x-assets/css/theme.min.css" as="style">
    <link rel="preload" href="/x-assets/css/theme.rtl.min.css" as="style">
    <link rel="stylesheet" href="/x-assets/css/theme.min.css" id="theme-styles">
  </head>


  <!-- Body -->
  <body>

    <!-- Shopping cart offcanvas (Empty state) -->
    <div class="offcanvas offcanvas-end pb-sm-2 px-sm-2" id="shoppingCart" tabindex="-1" aria-labelledby="shoppingCartLabel" style="width: 500px">
      <div class="offcanvas-header py-3 pt-lg-4">
        <h4 class="offcanvas-title" id="shoppingCartLabel">Shopping cart</h4>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body text-center">
        <svg class="d-block mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" width="60" viewBox="0 0 29.5 30"><path class="text-body-tertiary" d="M17.8 4c.4 0 .8-.3.8-.8v-2c0-.4-.3-.8-.8-.8-.4 0-.8.3-.8.8v2c0 .4.3.8.8.8zm3.2.6c.4.2.8 0 1-.4l.4-.9c.2-.4 0-.8-.4-1s-.8 0-1 .4l-.4.9c-.2.4 0 .9.4 1zm-7.5-.4c.2.4.6.6 1 .4s.6-.6.4-1l-.4-.9c-.2-.4-.6-.6-1-.4s-.6.6-.4 1l.4.9z" fill="currentColor"/><path class="text-body-emphasis" d="M10.7 24.5c-1.5 0-2.8 1.2-2.8 2.8S9.2 30 10.7 30s2.8-1.2 2.8-2.8-1.2-2.7-2.8-2.7zm0 4c-.7 0-1.2-.6-1.2-1.2s.6-1.2 1.2-1.2 1.2.6 1.2 1.2-.5 1.2-1.2 1.2zm11.1-4c-1.5 0-2.8 1.2-2.8 2.8a2.73 2.73 0 0 0 2.8 2.8 2.73 2.73 0 0 0 2.8-2.8c0-1.6-1.3-2.8-2.8-2.8zm0 4c-.7 0-1.2-.6-1.2-1.2s.6-1.2 1.2-1.2 1.2.6 1.2 1.2-.6 1.2-1.2 1.2zM8.7 18h16c.3 0 .6-.2.7-.5l4-10c.2-.5-.2-1-.7-1H9.3c-.4 0-.8.3-.8.8s.4.7.8.7h18.3l-3.4 8.5H9.3L5.5 1C5.4.7 5.1.5 4.8.5h-4c-.5 0-.8.3-.8.7s.3.8.8.8h3.4l3.7 14.6a3.24 3.24 0 0 0-2.3 3.1C5.5 21.5 7 23 8.7 23h16c.4 0 .8-.3.8-.8s-.3-.8-.8-.8h-16a1.79 1.79 0 0 1-1.8-1.8c0-1 .9-1.6 1.8-1.6z" fill="currentColor"/></svg>
        <h6 class="mb-2">Your shopping cart is currently empty!</h6>
        <p class="fs-sm mb-4">Explore our wide range of products and add items to your cart to proceed with your purchase.</p>
        <a class="btn btn-dark rounded-pill" href="shop-catalog-furniture.html">Continue shopping</a>
      </div>
    </div>





<!-- Topbar (kod2 + slider z kod1 uprostřed) -->
<div class="container position-relative d-flex justify-content-between align-items-center z-1 py-3">

  <!-- Left: contact -->
  <div class="nav animate-underline">
    <span class="text-secondary-emphasis fs-xs me-1 hide-mobile">
      <?= t('top.v1', 'Contact us') ?> <span class="d-none d-sm-inline">9-16</span>
    </span>
    <a class="nav-link animate-target fs-xs fw-semibold p-0 " href="tel:+420603143585">+420&nbsp;603&nbsp;143&nbsp;585</a>
  </div>

  <!-- Center: ONLY the scrolling 3 texts from kod1 -->
  <div class="d-flex align-items-center flex-nowrap mx-3" style="min-width:0; max-width:520px;">
    <div class="nav me-2">
      <button type="button" class="nav-link fs-sm p-0" id="topbarPrev" aria-label="Prev">
        <i class="ci-chevron-left" aria-hidden="true"></i>
        <!-- nebo: &lsaquo; -->
      </button>
    </div>

    <div class="swiper fs-xs text-secondary-emphasis" data-swiper='{
      "spaceBetween": 24,
      "loop": true,
      "autoplay": {
        "delay": 5000,
        "disableOnInteraction": false
      },
      "navigation": {
        "prevEl": "#topbarPrev",
        "nextEl": "#topbarNext"
      }
    }' style="min-width:0;">
      <div class="swiper-wrapper">
        <div class="swiper-slide text-truncate text-center">
          💰 <?= t('top.v21', 'Free Shipping on orders over $400.') ?> <span class="d-none d-sm-inline"><?= t('top.v211', 'Do not miss a discount!') ?></span>
        </div>
        <div class="swiper-slide text-truncate text-center">
          🎉 <?= t('top.v22', 'Money back guarantee.') ?> <span class="d-none d-sm-inline"><?= t('top.v221', 'We return money within 30 days.') ?></span>
        </div>
        <div class="swiper-slide text-truncate text-center">
          💪 <?= t('top.v23', 'Friendly 9-16 customer support.') ?> <span class="d-none d-sm-inline"><?= t('top.v231', 'We have got you covered!') ?></span>
        </div>
      </div>
    </div>

    <div class="nav ms-2">
      <button type="button" class="nav-link fs-sm p-0" id="topbarNext" aria-label="Next">
        <i class="ci-chevron-right" aria-hidden="true"></i>
        <!-- nebo: &rsaquo; -->
      </button>
    </div>
  </div>


  <!-- Right: nav -->
  <ul class="nav gap-4 hide-mobile">
    <li class="animate-underline">
      <a class="nav-link animate-target fs-xs p-0" href="/shop/<?= $lang ?>/content/4-about-us"><?= t('nav.a0', 'ABOUT US') ?></a>
    </li>
    <li class="animate-underline">
      <a class="nav-link animate-target fs-xs p-0" href="/shop/<?= $lang ?>/<?= t('url.contact', 'contact-us') ?>"><?= t('nav.c0', 'CONTACT') ?></a>
    </li>
  </ul>
</div>



    <!-- Navigation bar (Page header) -->
    <header class="navbar-sticky sticky-top container z-fixed px-2" data-sticky-element>
      <div class="navbar navbar-expand-lg flex-nowrap bg-body rounded-pill shadow ps-0 mx-1">
        <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark rounded-pill z-0 d-none d-block-dark"></div>

        <!-- Mobile offcanvas menu toggler (Hamburger) -->
        <button type="button" class="navbar-toggler ms-3" data-bs-toggle="offcanvas" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar brand (Logo) -->
        <a class="navbar-brand position-relative z-1 ms-4 ms-sm-5 ms-lg-4 me-2 me-sm-0 me-lg-3" href="en">
          <img src="/x-assets/img/zan-logo.png" alt="ZAN-AROMI" style="height:30px; width:auto">
        </a>
        <!-- Main navigation that turns into offcanvas on screens < 992px wide (lg breakpoint) -->
        <nav class="offcanvas offcanvas-start" id="navbarNav" tabindex="-1" aria-labelledby="navbarNavLabel">
          <div class="offcanvas-header py-3">
            <h5 class="offcanvas-title" id="navbarNavLabel">ZAN-AROMI</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body pt-3 pb-4 py-lg-0 mx-lg-auto">
            <ul class="navbar-nav position-relative">
              
              <!-- @mf nav home
              <li class="nav-item dropdown me-lg-n1 me-xl-0">
                <a class="nav-link dropdown-toggle fs-sm active" aria-current="page" href="#" role="button" data-bs-toggle="dropdown" data-bs-trigger="hover" aria-expanded="false">Home</a>
                <ul class="dropdown-menu" style="--cz-dropdown-spacer: 1rem">
                  <li class="hover-effect-opacity px-2 mx-n2">
                    <a class="dropdown-item d-block mb-0" href="home-electronics.html">
                      <span class="fw-medium">Electronics Store</span>
                      <span class="d-block fs-xs text-body-secondary">Megamenu + Hero slider</span>
                      <div class="d-none d-lg-block hover-effect-target position-absolute top-0 start-100 bg-body border border-light-subtle rounded rounded-start-0 transition-none invisible opacity-0 pt-2 px-2 ms-n2" style="width: 212px; height: calc(100% + 2px); margin-top: -1px">
                        <img class="position-relative z-2 d-none-dark" src="/x-assets/img/mega-menu/demo-preview/electronics-light.jpg" alt="Electronics Store">
                        <img class="position-relative z-2 d-none d-block-dark" src="/x-assets/img/mega-menu/demo-preview/electronics-dark.jpg" alt="Electronics Store">
                        <span class="position-absolute top-0 start-0 w-100 h-100 rounded rounded-start-0 d-none-dark" style="box-shadow: .875rem .5rem 2rem -.5rem #676f7b; opacity: .1"></span>
                        <span class="position-absolute top-0 start-0 w-100 h-100 rounded rounded-start-0 d-none d-block-dark" style="box-shadow: .875rem .5rem 1.875rem -.5rem #080b12; opacity: .25"></span>
                      </div>
                    </a>
                  </li>
                  <li class="hover-effect-opacity px-2 mx-n2">
                    <a class="dropdown-item d-block mb-0" href="home-fashion-v1.html">
                      <span class="fw-medium">Fashion Store v.1</span>
                      <span class="d-block fs-xs text-body-secondary">Hero promo slider</span>
                      <div class="d-none d-lg-block hover-effect-target position-absolute top-0 start-100 bg-body border border-light-subtle rounded rounded-start-0 transition-none invisible opacity-0 pt-2 px-2 ms-n2" style="width: 212px; height: calc(100% + 2px); margin-top: -1px">
                        <img class="position-relative z-2 d-none-dark" src="/x-assets/img/mega-menu/demo-preview/fashion-1-light.jpg" alt="Fashion Store v.1">
                        <img class="position-relative z-2 d-none d-block-dark" src="/x-assets/img/mega-menu/demo-preview/fashion-1-dark.jpg" alt="Fashion Store v.1">
                        <span class="position-absolute top-0 start-0 w-100 h-100 rounded rounded-start-0 d-none-dark" style="box-shadow: .875rem .5rem 2rem -.5rem #676f7b; opacity: .1"></span>
                        <span class="position-absolute top-0 start-0 w-100 h-100 rounded rounded-start-0 d-none d-block-dark" style="box-shadow: .875rem .5rem 1.875rem -.5rem #080b12; opacity: .25"></span>
                      </div>
                    </a>
                  </li>
                  <li class="hover-effect-opacity px-2 mx-n2">
                    <a class="dropdown-item d-block mb-0" href="home-fashion-v2.html">
                      <span class="fw-medium">Fashion Store v.2</span>
                      <span class="d-block fs-xs text-body-secondary">Hero banner with hotspots</span>
                      <div class="d-none d-lg-block hover-effect-target position-absolute top-0 start-100 bg-body border border-light-subtle rounded rounded-start-0 transition-none invisible opacity-0 pt-2 px-2 ms-n2" style="width: 212px; height: calc(100% + 2px); margin-top: -1px">
                        <img class="position-relative z-2 d-none-dark" src="/x-assets/img/mega-menu/demo-preview/fashion-2-light.jpg" alt="Fashion Store v.2">
                        <img class="position-relative z-2 d-none d-block-dark" src="/x-assets/img/mega-menu/demo-preview/fashion-2-dark.jpg" alt="Fashion Store v.2">
                        <span class="position-absolute top-0 start-0 w-100 h-100 rounded rounded-start-0 d-none-dark" style="box-shadow: .875rem .5rem 2rem -.5rem #676f7b; opacity: .1"></span>
                        <span class="position-absolute top-0 start-0 w-100 h-100 rounded rounded-start-0 d-none d-block-dark" style="box-shadow: .875rem .5rem 1.875rem -.5rem #080b12; opacity: .25"></span>
                      </div>
                    </a>
                  </li>
                  <li class="hover-effect-opacity px-2 mx-n2">
                    <a class="dropdown-item d-block mb-0" href="home-furniture.html">
                      <span class="fw-medium">Furniture Store</span>
                      <span class="d-block fs-xs text-body-secondary">Fancy product carousel</span>
                      <div class="d-none d-lg-block hover-effect-target position-absolute top-0 start-100 bg-body border border-light-subtle rounded rounded-start-0 transition-none invisible opacity-0 pt-2 px-2 ms-n2" style="width: 212px; height: calc(100% + 2px); margin-top: -1px">
                        <img class="position-relative z-2 d-none-dark" src="/x-assets/img/mega-menu/demo-preview/furniture-light.jpg" alt="Furniture Store">
                        <img class="position-relative z-2 d-none d-block-dark" src="/x-assets/img/mega-menu/demo-preview/furniture-dark.jpg" alt="Furniture Store">
                        <span class="position-absolute top-0 start-0 w-100 h-100 rounded rounded-start-0 d-none-dark" style="box-shadow: .875rem .5rem 2rem -.5rem #676f7b; opacity: .1"></span>
                        <span class="position-absolute top-0 start-0 w-100 h-100 rounded rounded-start-0 d-none d-block-dark" style="box-shadow: .875rem .5rem 1.875rem -.5rem #080b12; opacity: .25"></span>
                      </div>
                    </a>
                  </li>
                  <li class="hover-effect-opacity px-2 mx-n2">
                    <a class="dropdown-item d-block mb-0" href="home-grocery.html">
                      <span class="fw-medium">Grocery Store</span>
                      <span class="d-block fs-xs text-body-secondary">Hero slider + Category cards</span>
                      <div class="d-none d-lg-block hover-effect-target position-absolute top-0 start-100 bg-body border border-light-subtle rounded rounded-start-0 transition-none invisible opacity-0 pt-2 px-2 ms-n2" style="width: 212px; height: calc(100% + 2px); margin-top: -1px">
                        <img class="position-relative z-2 d-none-dark" src="/x-assets/img/mega-menu/demo-preview/grocery-light.jpg" alt="Grocery Store">
                        <img class="position-relative z-2 d-none d-block-dark" src="/x-assets/img/mega-menu/demo-preview/grocery-dark.jpg" alt="Grocery Store">
                        <span class="position-absolute top-0 start-0 w-100 h-100 rounded rounded-start-0 d-none-dark" style="box-shadow: .875rem .5rem 2rem -.5rem #676f7b; opacity: .1"></span>
                        <span class="position-absolute top-0 start-0 w-100 h-100 rounded rounded-start-0 d-none d-block-dark" style="box-shadow: .875rem .5rem 1.875rem -.5rem #080b12; opacity: .25"></span>
                      </div>
                    </a>
                  </li>
                  <li class="hover-effect-opacity px-2 mx-n2">
                    <a class="dropdown-item d-block mb-0" href="home-marketplace.html">
                      <span class="fw-medium">Marketplace</span>
                      <span class="d-block fs-xs text-body-secondary">Multi-vendor, digital goods</span>
                      <div class="d-none d-lg-block hover-effect-target position-absolute top-0 start-100 bg-body border border-light-subtle rounded rounded-start-0 transition-none invisible opacity-0 pt-2 px-2 ms-n2" style="width: 212px; height: calc(100% + 2px); margin-top: -1px">
                        <img class="position-relative z-2 d-none-dark" src="/x-assets/img/mega-menu/demo-preview/marketplace-light.jpg" alt="Marketplace">
                        <img class="position-relative z-2 d-none d-block-dark" src="/x-assets/img/mega-menu/demo-preview/marketplace-dark.jpg" alt="Marketplace">
                        <span class="position-absolute top-0 start-0 w-100 h-100 rounded rounded-start-0 d-none-dark" style="box-shadow: .875rem .5rem 2rem -.5rem #676f7b; opacity: .1"></span>
                        <span class="position-absolute top-0 start-0 w-100 h-100 rounded rounded-start-0 d-none d-block-dark" style="box-shadow: .875rem .5rem 1.875rem -.5rem #080b12; opacity: .25"></span>
                      </div>
                    </a>
                  </li>
                  <li class="hover-effect-opacity px-2 mx-n2">
                    <a class="dropdown-item d-block mb-0" href="home-single-store.html">
                      <span class="fw-medium">Single Product Store</span>
                      <span class="d-block fs-xs text-body-secondary">Single product / mono brand</span>
                      <div class="d-none d-lg-block hover-effect-target position-absolute top-0 start-100 bg-body border border-light-subtle rounded rounded-start-0 transition-none invisible opacity-0 pt-2 px-2 ms-n2" style="width: 212px; height: calc(100% + 2px); margin-top: -1px">
                        <img class="position-relative z-2 d-none-dark" src="/x-assets/img/mega-menu/demo-preview/single-store-light.jpg" alt="Single Product Store">
                        <img class="position-relative z-2 d-none d-block-dark" src="/x-assets/img/mega-menu/demo-preview/single-store-dark.jpg" alt="Single Product Store">
                        <span class="position-absolute top-0 start-0 w-100 h-100 rounded rounded-start-0 d-none-dark" style="box-shadow: .875rem .5rem 2rem -.5rem #676f7b; opacity: .1"></span>
                        <span class="position-absolute top-0 start-0 w-100 h-100 rounded rounded-start-0 d-none d-block-dark" style="box-shadow: .875rem .5rem 1.875rem -.5rem #080b12; opacity: .25"></span>
                      </div>
                    </a>
                  </li>
                </ul>
              </li>
              -->
              <!-- @mf nav aromata -->
              <li class="nav-item dropdown position-static me-lg-n1 me-xl-0">
                <a class="nav-link dropdown-toggle fs-sm" href="/shop/<?= $lang ?>/10-flavours" role="button" data-bs-toggle="dropdown" data-bs-trigger="hover" aria-expanded="false"><?= t('nav.f0', 'FLAVOURS') ?></a>
                <div class="dropdown-menu p-4" style="--cz-dropdown-spacer: 1rem">
                  <div class="d-flex flex-column flex-lg-row gap-4">
                    <div style="min-width: 190px">
                      <div class="h6 mb-2"><?= t('nav.f01', 'BY DRINK') ?></div>
                      <ul class="nav flex-column gap-2 mt-0">
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/29-spirits"><?= t('nav.f011', 'Spirits') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/30-fruit-distillates"><?= t('nav.f012', 'Fruit Distillates') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/31-liqueurs"><?= t('nav.f013', 'Liqueurs') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/32-wine-beverages"><?= t('nav.f014', 'Wine Beverages') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/33-beer"><?= t('nav.f015', 'Beer') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/34-mead"><?= t('nav.f016', 'Mead') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/35-non-alcoholic-beverages"><?= t('nav.f017', 'Non-Alcoholic Beverages') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/36-special-drinks"><?= t('nav.f018', 'Special Drinks') ?></a>
                        </li>
                      </ul>
                  </div>
                  <div style="min-width: 190px">
                      <div class="h6 mb-2"><?= t('nav.f02', 'BY TYPE') ?></div>
                      <ul class="nav flex-column gap-2 mt-0">
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/24-fruits"><?= t('nav.f021', 'Fruits') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/25-herbs"><?= t('nav.f022', 'Herbs') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/26-spice"><?= t('nav.f023', 'Spice') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/27-sweets"><?= t('nav.f024', 'Sweets') ?></a>
                        </li>
                       </ul> 
                      <div class="h6 pt-4 mb-2"><?= t('nav.f03', 'BY BASE') ?></div>
                      <ul class="nav flex-column gap-2 mt-0">
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/13-alcohol"><?= t('nav.f031', 'Alcohol') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/14-propylene-glycol"><?= t('nav.f032', 'Propylene Glycol') ?></a>
                        </li> 
                      </ul>
                    </div>  
                    <div style="min-width: 190px">
                      <div class="h6 mb-2"><?= t('nav.f04', 'BY CLASS') ?></div>
                      <ul class="nav flex-column gap-2 mt-0">
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/16-flavour"><?= t('nav.f041', 'Flavour') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/17-tincture"><?= t('nav.f042', 'Tincture') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/18-essential-oil"><?= t('nav.f043', 'Essential Oil') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/19-food-colors-and-others"><?= t('nav.f044', 'Food Colors & Others') ?></a>
                        </li>
                      </ul>
                      <div class="h6 pt-4 mb-2"><?= t('nav.f05', 'BY COMPOSITION') ?></div>
                      <ul class="nav flex-column gap-2 mt-0">
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/21-naturally-identical"><?= t('nav.f051', 'Naturally Indentical') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/22-purely-natural"><?= t('nav.f052', 'Purely Natural') ?></a>
                        </li>
                      </ul>
                    </div>
                    <div style="min-width: 190px">
                      <div class="h6 mb-2"><?= t('nav.f06', 'BY USE') ?></div>
                      <ul class="nav flex-column gap-2 mt-0">
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/38-beverages"><?= t('nav.f061', 'Beverages') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/39-baking-confectionery"><?= t('nav.f062', 'Baking & Confectionery') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/40-fishing"><?= t('nav.f063', 'Fishing') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/41-others"><?= t('nav.f064', 'Others') ?></a>
                        </li>
                      </ul>
                    </div>
                </div>
              </div>
              </li>
              <!-- @mf nav vinarstvi -->
              <li class="nav-item dropdown position-static me-lg-n1 me-xl-0">
                <a class="nav-link dropdown-toggle fs-sm" href="/shop/<?= $lang ?>/11-viticulture" role="button" data-bs-toggle="dropdown" data-bs-trigger="hover" aria-expanded="false"><?= t('nav.w0', 'VITICULTURE') ?></a>
                <div class="dropdown-menu p-4" style="--cz-dropdown-spacer: 1rem">
                  <div class="d-flex flex-column flex-lg-row gap-4">
                    <div style="min-width: 190px">
                      <div class="h6 mb-2"><?= t('nav.w01', 'FERMENTATION') ?></div>
                      <ul class="nav flex-column gap-2 mt-0">
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/50-yeast"><?= t('nav.w011', 'Yeast') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/51-cell-wall-of-yeast"><?= t('nav.w012', 'Cell Walls of Yeast') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/52-nutrients"><?= t('nav.w013', 'Nutrients') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/53-mlf"><?= t('nav.w014', 'MLF') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/54-enzymes"><?= t('nav.w015', 'Enzymes') ?></a>
                        </li>
                        </ul> 
                      <div class="h6 pt-4 mb-2"><?= t('nav.w02', 'FINING') ?></div>
                      <ul class="nav flex-column gap-2 mt-0">
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/130-tannins"><?= t('nav.w021', 'Tannins') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/131-acids-and-others"><?= t('nav.w022', 'Acids & Other') ?></a>
                        </li> 
                      </ul>
                    </div>  
                  <div style="min-width: 190px">
                      <div class="h6 mb-2"><?= t('nav.w03', 'STABILIZATION') ?></div>
                      <ul class="nav flex-column gap-2 mt-0">
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/57-sedimentation"><?= t('nav.w031', 'Sedimentation') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/60-taniny"><?= t('nav.w032', 'Tannins') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/59-oxidation"><?= t('nav.w033', 'Oxidation') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/58-color-and-aroma"><?= t('nav.w034', 'Color & Aroma') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/61-biology"><?= t('nav.w035', 'Biology') ?></a>
                        </li>
                        </ul>
                      <div class="h6 pt-4 mb-2"><?= t('nav.w04', 'SULFUTATION') ?></div>
                      <ul class="nav flex-column gap-2 mt-0">
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/55-sulfur"><?= t('nav.w041', 'Sulfur') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/56-pyrosulfite"><?= t('nav.w042', 'Pyrosulfite') ?></a>
                        </li> 
                      </ul>
                    </div>
                    <div style="min-width: 190px">
                      <div class="h6 mb-2"><?= t('nav.w05', 'CLARIFICATION') ?></div>
                      <ul class="nav flex-column gap-2 mt-0">
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/64-bentonites"><?= t('nav.w051', 'Bentonites') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/65-proteins"><?= t('nav.w052', 'Proteins') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/66-gelatins"><?= t('nav.w053', 'Gelatins') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/67-silicate-salts"><?= t('nav.w054', 'Silicate Salts') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/68-complex-agents"><?= t('nav.w055', 'Complex Agents') ?></a>
                        </li>
                        </ul> 
                      <div class="h6 pt-4 mb-2"><?= t('nav.w06', 'FILTRATION') ?></div>
                      <ul class="nav flex-column gap-2 mt-0">
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/70-kieselguhr-filter"><?= t('nav.w061', 'Kieselguhr Filter') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/69-fossil-kieselguhr"><?= t('nav.w062', 'Fossil Kieselguhr') ?></a>
                        </li> 
                      </ul>
                    </div>
                    <div style="min-width: 190px">
                      <div class="h6 mb-2"><?= t('nav.w07', 'IMPORT') ?></div>
                      <ul class="nav flex-column gap-2 mt-0">
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/72-grape-concentrates-mcrt"><?= t('nav.w071', 'Grape Concentrates') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/73-grape-musts"><?= t('nav.w072', 'Grape Musts') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/71-italian-wines"><?= t('nav.w073', 'Italian Wines') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/77-machines-and-equipments"><?= t('nav.w074', 'Machines & Equipments') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/48-accessories"><?= t('nav.w075', 'Accessories') ?></a>
                        </li>
                        </ul> 
                      <div class="h6 pt-4 mb-2"><?= t('nav.w08', 'DISINFECTION') ?></div>
                      <ul class="nav flex-column gap-2 mt-0">
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/62-detergents"><?= t('nav.w081', 'Detergents') ?></a>
                        </li>
                        <li class="d-flex w-100 pt-1">
                          <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/63-sanitation-agents"><?= t('nav.w082', 'Sanitation Agents') ?></a>
                        </li> 
                      </ul>
                    </div>
                </div>
              </div>
              </li>
              <!-- @mf nav shop -->
              <li class="nav-item me-lg-n2 me-xl-0">
                <a class="nav-link fs-sm" href="/shop/<?= $lang ?>" role="button" data-bs-trigger="hover" aria-expanded="false"><?= t('nav.s0', 'SHOP') ?></a>
              </li>  
              <!-- @mf nav o nas
              <li class="nav-item me-lg-n2 me-xl-0">
                <a class="nav-link fs-sm" href="/shop/<?= $lang ?>/content/4-about-us" role="button" data-bs-trigger="hover" aria-expanded="false"><?= t('nav.a0', 'ABOUT US') ?></a>
              </li>
              -->  
              <!-- @mf nav kontakt 
              <li class="nav-item me-lg-n2 me-xl-0">
                <a class="nav-link fs-sm" href="/shop/<?= $lang ?>/<?= t('url.contact', 'contact-us') ?>" role="button" data-bs-trigger="hover" aria-expanded="false"><?= t('nav.c0', 'CONTACT') ?></a>
              </li> 
              --> 
              <!-- @mf nav novinky -->
              <li class="nav-item me-lg-n2 me-xl-0">
                <a class="nav-link fs-sm" href="/shop/<?= $lang ?>/134-new-arrivals-new" role="button" data-bs-trigger="hover" aria-expanded="false"><?= t('nav.na0', 'NEW ARRIVALS') ?></a>
              </li>  
              <!-- @mf nav akce, slevy -->
              <li class="nav-item me-lg-n2 me-xl-0">
                <a class="nav-link fs-sm" href="/shop/<?= $lang ?>/132-hot-of-the-day" role="button" data-bs-trigger="hover" aria-expanded="false"><?= t('nav.d0', 'DISCOUNTS') ?></a>
              </li>  
              <!-- @mf nav kontakt
              <li class="nav-item dropdown me-lg-n1 me-xl-0">
                <a class="nav-link dropdown-toggle fs-sm" href="#" role="button" data-bs-toggle="dropdown" data-bs-trigger="hover" data-bs-auto-close="outside" aria-expanded="false">Account</a>
                <ul class="dropdown-menu" style="--cz-dropdown-spacer: 1rem">
                  <li class="dropend">
                    <a class="dropdown-item dropdown-toggle" href="#!" role="button" data-bs-toggle="dropdown" data-bs-trigger="hover" aria-expanded="false">Auth Pages</a>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item" href="account-signin.html">Sign In</a></li>
                      <li><a class="dropdown-item" href="account-signup.html">Sign Up</a></li>
                      <li><a class="dropdown-item" href="account-password-recovery.html">Password Recovery</a></li>
                    </ul>
                  </li>
                  <li class="dropend">
                    <a class="dropdown-item dropdown-toggle" href="#!" role="button" data-bs-toggle="dropdown" data-bs-trigger="hover" aria-expanded="false">Shop User</a>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item" href="account-orders.html">Orders History</a></li>
                      <li><a class="dropdown-item" href="account-wishlist.html">Wishlist</a></li>
                      <li><a class="dropdown-item" href="account-payment.html">Payment Methods</a></li>
                      <li><a class="dropdown-item" href="account-reviews.html">My Reviews</a></li>
                      <li><a class="dropdown-item" href="account-info.html">Personal Info</a></li>
                      <li><a class="dropdown-item" href="account-addresses.html">Addresses</a></li>
                      <li><a class="dropdown-item" href="account-notifications.html">Notifications</a></li>
                    </ul>
                  </li>
                  <li class="dropend">
                    <a class="dropdown-item dropdown-toggle" href="#!" role="button" data-bs-toggle="dropdown" data-bs-trigger="hover" aria-expanded="false">Marketplace User</a>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item" href="account-marketplace-dashboard.html">Dashboard</a></li>
                      <li><a class="dropdown-item" href="account-marketplace-products.html">Products</a></li>
                      <li><a class="dropdown-item" href="account-marketplace-sales.html">Sales</a></li>
                      <li><a class="dropdown-item" href="account-marketplace-payouts.html">Payouts</a></li>
                      <li><a class="dropdown-item" href="account-marketplace-purchases.html">Purchases</a></li>
                      <li><a class="dropdown-item" href="account-marketplace-favorites.html">Favorites</a></li>
                      <li><a class="dropdown-item" href="account-marketplace-settings.html">Settings</a></li>
                    </ul>
                  </li>
                </ul>
              </li>
              <li class="nav-item dropdown me-lg-n1 me-xl-0">
                <a class="nav-link dropdown-toggle fs-sm" href="#" role="button" data-bs-toggle="dropdown" data-bs-trigger="hover" data-bs-auto-close="outside" aria-expanded="false">Pages</a>
                <ul class="dropdown-menu" style="--cz-dropdown-spacer: 1rem">
                  <li class="dropend">
                    <a class="dropdown-item dropdown-toggle" href="#!" role="button" data-bs-toggle="dropdown" data-bs-trigger="hover" aria-expanded="false">About</a>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item" href="about-v1.html">About v.1</a></li>
                      <li><a class="dropdown-item" href="about-v2.html">About v.2</a></li>
                    </ul>
                  </li>
                  <li class="dropend">
                    <a class="dropdown-item dropdown-toggle" href="#!" role="button" data-bs-toggle="dropdown" data-bs-trigger="hover" aria-expanded="false">Blog</a>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item" href="blog-grid-v1.html">Grid View v.1</a></li>
                      <li><a class="dropdown-item" href="blog-grid-v2.html">Grid View v.2</a></li>
                      <li><a class="dropdown-item" href="blog-list.html">List View</a></li>
                      <li><a class="dropdown-item" href="blog-single-v1.html">Single Post v.1</a></li>
                      <li><a class="dropdown-item" href="blog-single-v2.html">Single Post v.2</a></li>
                    </ul>
                  </li>
                  <li class="dropend">
                    <a class="dropdown-item dropdown-toggle" href="#!" role="button" data-bs-toggle="dropdown" data-bs-trigger="hover" aria-expanded="false">Contact</a>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item" href="contact-v1.html">Contact v.1</a></li>
                      <li><a class="dropdown-item" href="contact-v2.html">Contact v.2</a></li>
                      <li><a class="dropdown-item" href="contact-v3.html">Contact v.3</a></li>
                    </ul>
                  </li>
                  <li class="dropend">
                    <a class="dropdown-item dropdown-toggle" href="#!" role="button" data-bs-toggle="dropdown" data-bs-trigger="hover" aria-expanded="false">Help Center</a>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item" href="help-topics-v1.html">Help Topics v.1</a></li>
                      <li><a class="dropdown-item" href="help-topics-v2.html">Help Topics v.2</a></li>
                      <li><a class="dropdown-item" href="help-single-article-v1.html">Help Single Article v.1</a></li>
                      <li><a class="dropdown-item" href="help-single-article-v2.html">Help Single Article v.2</a></li>
                    </ul>
                  </li>
                  <li class="dropend">
                    <a class="dropdown-item dropdown-toggle" href="#!" role="button" data-bs-toggle="dropdown" data-bs-trigger="hover" aria-expanded="false">404 Error</a>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item" href="404-electronics.html">404 Electronics</a></li>
                      <li><a class="dropdown-item" href="404-fashion.html">404 Fashion</a></li>
                      <li><a class="dropdown-item" href="404-furniture.html">404 Furniture</a></li>
                      <li><a class="dropdown-item" href="404-grocery.html">404 Grocery</a></li>
                    </ul>
                  </li>
                  <li><a class="dropdown-item" href="terms-and-conditions.html">Terms &amp; Conditions</a></li>
                </ul>
              </li>
              <li class="nav-item me-lg-n2 me-xl-0">
                <a class="nav-link fs-sm" href="/x-docs/installation.html">Docs</a>
              </li>
              <li class="nav-item me-lg-n2 me-xl-0">
                <a class="nav-link fs-sm" href="/x-docs/typography.html">Components</a>
              </li>
      
        -->    
            </ul>
          </div>
        </nav>

        <!-- Button group -->
        <div class="d-flex gap-sm-1 position-relative z-1">

          <!-- Theme switcher (light/dark/auto) 
          <div class="dropdown">
            <button type="button" class="theme-switcher btn btn-icon btn-outline-secondary fs-lg border-0 rounded-circle animate-scale" data-bs-toggle="dropdown" data-bs-display="dynamic" aria-expanded="false" aria-label="Toggle theme (light)">
              <span class="theme-icon-active d-flex animate-target">
                <i class="ci-sun"></i>
              </span>
            </button>
            <ul class="dropdown-menu start-50 translate-middle-x" style="--cz-dropdown-min-width: 9rem; --cz-dropdown-spacer: 1rem">
              <li>
                <button type="button" class="dropdown-item active" data-bs-theme-value="light" aria-pressed="true">
                  <span class="theme-icon d-flex fs-base me-2">
                    <i class="ci-sun"></i>
                  </span>
                  <span class="theme-label">Light</span>
                  <i class="item-active-indicator ci-check ms-auto"></i>
                </button>
              </li>
              <li>
                <button type="button" class="dropdown-item" data-bs-theme-value="dark" aria-pressed="false">
                  <span class="theme-icon d-flex fs-base me-2">
                    <i class="ci-moon"></i>
                  </span>
                  <span class="theme-label">Dark</span>
                  <i class="item-active-indicator ci-check ms-auto"></i>
                </button>
              </li>
              <li>
                <button type="button" class="dropdown-item" data-bs-theme-value="auto" aria-pressed="false">
                  <span class="theme-icon d-flex fs-base me-2">
                    <i class="ci-auto"></i>
                  </span>
                  <span class="theme-label">Auto</span>
                  <i class="item-active-indicator ci-check ms-auto"></i>
                </button>
              </li>
            </ul>
          </div>
          -->
          <!-- Cart button 
          <button type="button" class="btn btn-icon fs-lg btn-outline-secondary border-0 rounded-circle animate-scale me-2" data-bs-toggle="offcanvas" data-bs-target="#shoppingCart" aria-controls="shoppingCart" aria-label="Shopping cart">
            <i class="ci-shopping-cart animate-target"></i>
          </button>
          -->
          <!-- Search 
          <div class="dropdown">
            <button type="button" class="btn btn-icon fs-lg btn-secondary rounded-circle animate-scale" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-label="Toggle search bar">
              <i class="ci-search animate-target"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end p-3" style="--cz-dropdown-min-width: 20rem; --cz-dropdown-spacer: 1rem">
              <form class="position-relative">
                <input type="search" class="form-control rounded-pill" placeholder="Search..." data-autofocus="dropdown">
                <button type="submit" class="btn btn-icon btn-sm fs-lg btn-secondary rounded-circle position-absolute top-0 end-0 mt-1 me-1" aria-label="Search">
                  <i class="ci-arrow-right"></i>
                </button>
              </form>
            </div>
          </div>
          -->
          <!-- Country select visible on screens > 768px wide (md breakpoint) -->
          <!-- bylo jen do <768px
          <div class="dropdown d-none d-md-block nav">
          -->
          <?php
// Bezpecne zjisti aktualni path (muze/ne musi existovat)
$path = isset($path) ? (string)$path : '';

// Mapa jazyku → vlajka + label (uprav dle nazvu souboru vlajek)
$flags = [
  'cs' => ['file' => 'cs.svg', 'label' => 'Čeština'],
  'de' => ['file' => 'de.svg', 'label' => 'Deutsch'],
  'gb' => ['file' => 'gb.svg', 'label' => 'English GB'],
  'fr' => ['file' => 'fr.svg', 'label' => 'Français'],
  'hu' => ['file' => 'hu.svg', 'label' => 'Magyar'],
  'pl' => ['file' => 'pl.svg', 'label' => 'Polski'],
  'ro' => ['file' => 'ro.svg', 'label' => 'Română'],
  'sk' => ['file' => 'sk.svg', 'label' => 'Slovenčina'],
];

// Aktualni jazyk/label/vlajka s fallbackem
$cur = $flags[$lang] ?? ['file' => 'gb.svg', 'label' => strtoupper($lang ?? 'GB')];

// Helper pro generovani URL /{lang}/{path?}
$hrefFor = function(string $code) use ($path): string {
  $p = trim($path, '/');
  return '/' . $code . ($p !== '' ? '/' . $p : '');
};
?>
<div class="dropdown block nav">
  <a class="nav-link dropdown-toggle py-1 px-0" href="#" data-bs-toggle="dropdown"
     aria-haspopup="true" aria-expanded="false"
     aria-label="Language: <?= htmlspecialchars($cur['label']) ?>">
    <div class="ratio ratio-1x1" style="width: 25px">
      <img src="x-assets/img/flags/<?= htmlspecialchars($cur['file']) ?>"
           alt="<?= htmlspecialchars($cur['label']) ?>">
    </div>
  </a>

  <ul class="dropdown-menu dropdown-menu-end fs-sm" style="--cz-dropdown-spacer: .5rem">
    <?php foreach ($flags as $code => $meta): ?>
      <li>
        <a class="dropdown-item <?= $code === $lang ? 'active' : '' ?>"
           href="<?= htmlspecialchars($hrefFor($code)) ?>">
          <img src="x-assets/img/flags/<?= htmlspecialchars($meta['file']) ?>"
               class="flex-shrink-0 me-2" width="20"
               alt="<?= htmlspecialchars($meta['label']) ?>">
          &nbsp;&nbsp;<?= htmlspecialchars($meta['label']) ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</div>


        </div>
      </div>
    </header>


    <!-- Page content -->
    <main class="content-wrapper">

      <!-- Hero slider -->
      <section class="bg-body-tertiary min-vh-100 d-flex align-items-center overflow-hidden" style="margin-top: -110px; padding-top: 110px">
        <div class="container h-100 py-5 my-md-2 my-lg-3 my-xl-4 mb-xxl-5">
          <h1 class="visually-hidden"><?= t('sli.h1', 'Flavorings for beverages and wine-making products for winemakers.') ?></h1>
          <p class="display-4 text-center mx-auto mb-4" style="max-width: 680px; color: #212529;"><?= t('sli.h1p', 'Everything You Need To Make Drinks') ?></p>
          <div class="row align-items-center justify-content-center gx-3 gx-sm-4 mb-3 mb-sm-4">

            <!-- Prev slide preview (controlled slider) -->
            <div class="col-lg-1 col-xl-2 d-none d-lg-flex justify-content-end">
              <div class="position-relative user-select-none" style="width: 262px">
                <span class="position-absolute top-0 start-0 w-100 h-100 bg-white opacity-50 rounded-circle d-none-dark"></span>
                <span class="position-absolute top-0 start-0 w-100 h-100 bg-white rounded-circle d-none-dark d-none d-block-dark" style="opacity: .05"></span>
                <div class="swiper position-relative z-2 opacity-60 rounded-circle pe-none" id="thumbsPrev" data-swiper='{
                  "allowTouchMove": false,
                  "loop": true,
                  "effect": "coverflow",
                  "coverflowEffect": {
                    "rotate": 0,
                    "scale": 1.3,
                    "depth": -200,
                    "stretch": -100,
                    "slideShadows": false
                  }
                }'>
                  <div class="swiper-wrapper">
                    <div class="swiper-slide">
                      <div class="ratio ratio-1x1">
                        <img src="/x-assets/img/home/flavor/hero-slider/04-vinbrule.png" alt="mulled-wine">
                      </div>
                    </div>
                    <div class="swiper-slide">
                      <div class="ratio ratio-1x1">
                        <img src="/x-assets/img/home/flavor/hero-slider/01-aperol-spritz.png" alt="aperol-spritz">
                      </div>
                    </div>
                    <div class="swiper-slide">
                      <div class="ratio ratio-1x1">
                        <img src="/x-assets/img/home/flavor/hero-slider/02-campari.png" alt="campari">
                      </div>
                    </div>
                    <div class="swiper-slide">
                      <div class="ratio ratio-1x1">
                        <img src="/x-assets/img/home/flavor/hero-slider/03-vermouth-bianco.png" alt="vermouth-bianco">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Prev button -->
            <div class="col-auto col-sm-1 order-1 order-lg-2 d-flex align-items-center justify-content-center">
              <button type="button" class="btn-prev btn btn-lg btn-icon btn-outline-secondary rounded-circle animate-slide-start" aria-label="Prev">
                <i class="ci-chevron-left fs-xl animate-target"></i>
              </button>
            </div>

            <!-- Main slider -->
            <div class="col-sm-10 col-lg-8 col-xl-6 order-3">
              <div class="swiper user-select-none rounded-pill" data-swiper='{
                "loop": true,
                "grabCursor": true,
                "speed": 600,
                "controlSlider": ["#thumbsPrev", "#thumbsNext", "#captions"],
                "effect": "coverflow",
                "coverflowEffect": {
                  "rotate": 0,
                  "scale": 1.3,
                  "depth": -200,
                  "stretch": -100,
                  "slideShadows": false
                },
                "navigation": {
                  "prevEl": ".btn-prev",
                  "nextEl": ".btn-next"
                },
                  "autoplay": {
                  "delay": 3500,
                  "disableOnInteraction": false,
                  "pauseOnMouseEnter": true
                }

              }'>
                <div class="swiper-wrapper">
                  <div class="swiper-slide" data-swiper-binded="#description1">
                    <div class="ratio" style="--cz-aspect-ratio: calc(400 / 636 * 100%)">
                      <img src="/x-assets/img/home/flavor/hero-slider/01M-aperol-spritz.png" alt="aperol-spritz" fetchpriority="high">
                    </div>
                  </div>
                  <div class="swiper-slide" data-swiper-binded="#description2">
                    <div class="ratio" style="--cz-aspect-ratio: calc(400 / 636 * 100%)">
                      <img src="/x-assets/img/home/flavor/hero-slider/02M-campari.png" alt="campari" fetchpriority="high">
                    </div>
                  </div>
                  <div class="swiper-slide" data-swiper-binded="#description3">
                    <div class="ratio" style="--cz-aspect-ratio: calc(400 / 636 * 100%)">
                      <img src="/x-assets/img/home/flavor/hero-slider/03M-vermouth-bianco.png" alt="vermouth-bianco" fetchpriority="high">
                    </div>
                  </div>
                  <div class="swiper-slide" data-swiper-binded="#description4">
                    <div class="ratio" style="--cz-aspect-ratio: calc(400 / 636 * 100%)">
                      <img src="/x-assets/img/home/flavor/hero-slider/04M-vinbrule.png" alt="mulled-wine" fetchpriority="high">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Next button -->
            <div class="col-auto col-sm-1 order-2 order-sm-3 order-lg-4 d-flex align-items-center justify-content-center">
              <button type="button" class="btn-next btn btn-lg btn-icon btn-outline-secondary rounded-circle animate-slide-end" aria-label="Next">
                <i class="ci-chevron-right fs-xl animate-target"></i>
              </button>
            </div>

            <!-- Next slide preview (controlled slider) -->
            <div class="col-lg-1 col-xl-2 order-lg-5 d-none d-lg-block">
              <div class="position-relative user-select-none" style="width: 262px">
                <span class="position-absolute top-0 start-0 w-100 h-100 bg-white opacity-50 rounded-circle d-none-dark"></span>
                <span class="position-absolute top-0 start-0 w-100 h-100 bg-white rounded-circle d-none-dark d-none d-block-dark" style="opacity: .05"></span>
                <div class="swiper position-relative z-2 opacity-60 rounded-circle pe-none" id="thumbsNext" data-swiper='{
                  "allowTouchMove": false,
                  "loop": true,
                  "effect": "coverflow",
                  "coverflowEffect": {
                    "rotate": 0,
                    "scale": 1.3,
                    "depth": -200,
                    "stretch": -100,
                    "slideShadows": false
                  }
                }'>
                  <div class="swiper-wrapper">
                    <div class="swiper-slide">
                      <div class="ratio ratio-1x1">
                        <img src="/x-assets/img/home/flavor/hero-slider/02-campari.png" alt="campari">
                      </div>
                    </div>
                    <div class="swiper-slide">
                      <div class="ratio ratio-1x1">
                        <img src="/x-assets/img/home/flavor/hero-slider/03-vermouth-bianco.png" alt="vermouth-bianco">
                      </div>
                    </div>
                    <div class="swiper-slide">
                      <div class="ratio ratio-1x1">
                        <img src="/x-assets/img/home/flavor/hero-slider/04-vinbrule.png" alt="mulled-wine">
                      </div>
                    </div>
                    <div class="swiper-slide">
                      <div class="ratio ratio-1x1">
                        <img src="/x-assets/img/home/flavor/hero-slider/01-aperol-spritz.png" alt="aperol-spritz">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Linked captions (controlled slider) -->
          <div class="swiper" id="captions" data-swiper='{
            "allowTouchMove": false,
            "loop": true,
            "effect": "fade"
          }'>
            <div class="swiper-wrapper">
              <div class="swiper-slide bg-body-tertiary text-center">
                <div class="h4 mb-2"><?= t('sli.pr1', 'Natural Flavour APERITIVO PD 1:100') ?></div>
                <div class="h4 mb-4" style="color:#DE1B1B;"><?= t('sli.pr1c', 'APER SPRITZ') ?></div>
                <a class="btn btn-lg btn-dark rounded-pill" href="/shop/<?= $lang ?>/aperitive/67-39-flavour-natural-aperitivo-pd-1100-aper-spritz.html">
                  <?= t('sli.pr1b', 'Shop now') ?>
                  <i class="ci-chevron-right fs-lg ms-2 me-n2"></i>
                </a>
              </div>
              <div class="swiper-slide bg-body-tertiary text-center">
                <div class="h4 mb-2"><?= t('sli.pr2', 'Natural Flavour BITTER 1:100') ?></div>
                <div class="h4 mb-4" style="color:#DE1B1B;"><?= t('sli.pr2c', 'CAMPAR') ?></div>
                <a class="btn btn-lg btn-dark rounded-pill" href="/shop/<?= $lang ?>/aperitive/220-437-flavour-natural-amaro-bitter-per-campari-1100.html">
                  <?= t('sli.pr2b', 'Shop now') ?>
                  <i class="ci-chevron-right fs-lg ms-2 me-n2"></i>
                </a>
              </div>
              <div class="swiper-slide bg-body-tertiary text-center">
                <div class="h4 mb-2"><?= t('sli.pr3', 'Natural Flavour VERMOUTH BIANCO 1:100') ?></div>
                <div class="h4 mb-4" style="color:#DE1B1B;"><?= t('sli.pr3c', 'VERMOUTH') ?></div>
                <a class="btn btn-lg btn-dark rounded-pill" href="/shop/<?= $lang ?>/aperitive/150-269-flavour-natural-vermouth-bianco-1100.html">
                  <?= t('sli.pr3b', 'Shop now') ?>
                  <i class="ci-chevron-right fs-lg ms-2 me-n2"></i>
                </a>
              </div>
              <div class="swiper-slide bg-body-tertiary text-center">
                <div class="h4 mb-2"><?= t('sli.pr4', 'Natural Flavour VINBRULE 1:5000') ?></div>
                <div class="h4 mb-4" style="color:#DE1B1B;"><?= t('sli.pr4c', 'MULLED WINE') ?></div>
                <a class="btn btn-lg btn-dark rounded-pill" href="/shop/<?= $lang ?>/wine-beverages/78-flavour-natural-vinbrule-mulled-wine-15000.html">
                  <?= t('sli.pr4b', 'Shop now') ?>
                  <i class="ci-chevron-right fs-lg ms-2 me-n2"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </section>



<!-- @mf small 6 category-->

<!-- Categories -->
    <section class="border-top">
      <div class="container py-lg-1">
        <div class="overflow-auto" data-simplebar>
          <div class="nav flex-nowrap justify-content-between gap-4 py-2">
            <a class="nav-link align-items-center animate-underline gap-2 p-0" href="/shop/<?= $lang ?>/134-new-arrivals-new">
              <span class="d-flex align-items-center justify-content-center bg-body-tertiary rounded-circle" style="width: 60px; height: 60px">
                <img src="/x-assets/img/mega-menu/grocery/th00.png" width="60" alt="Image">
              </span>
              <span class="d-block animate-target fw-semibold text-nowrap ms-1"><?= t('cat7.cir1', 'News!') ?></span>
            </a>
            <a class="nav-link align-items-center animate-underline gap-2 p-0" href="/shop/<?= $lang ?>/132-hot-of-the-day">
              <span class="d-flex align-items-center justify-content-center bg-body-tertiary rounded-circle" style="width: 60px; height: 60px">
                <img src="/x-assets/img/mega-menu/grocery/th01.png" width="60" alt="Image">
              </span>
              <span class="d-block animate-target fw-semibold text-nowrap ms-1"><?= t('cat7.cir2', 'Sale!') ?></span>
            </a>
            <a class="nav-link align-items-center animate-underline gap-2 p-0" href="/shop/<?= $lang ?>/19-food-colors-and-others">
              <span class="d-flex align-items-center justify-content-center bg-body-tertiary rounded-circle" style="width: 60px; height: 60px">
                <img src="/x-assets/img/mega-menu/grocery/th02.png" width="60" alt="Image">
              </span>
              <span class="d-block animate-target fw-semibold text-nowrap ms-1"><?= t('cat7.cir3', 'Food dyes') ?></span>
            </a>
            <a class="nav-link align-items-center animate-underline gap-2 m-0" href="/shop/<?= $lang ?>/135-turbo-yeast">
              <span class="d-flex align-items-center justify-content-center bg-body-tertiary rounded-circle" style="width: 60px; height: 60px">
                <img src="/x-assets/img/mega-menu/grocery/th03.png" width="60" alt="Image">
              </span>
              <span class="d-block animate-target fw-semibold text-nowrap ms-1"><?= t('cat7.cir4', 'Turbo Yeast') ?></span>
            </a>
            <a class="nav-link align-items-center animate-underline gap-2 p-0" href="/shop/<?= $lang ?>/72-grape-concentrates-mcr">
              <span class="d-flex align-items-center justify-content-center bg-body-tertiary rounded-circle" style="width: 60px; height: 60px">
                <img src="/x-assets/img/mega-menu/grocery/th04.png" width="60" alt="Image">
              </span>
              <span class="d-block animate-target fw-semibold text-nowrap ms-1"><?= t('cat7.cir5', 'Grape must') ?></span>
            </a>
            <a class="nav-link align-items-center animate-underline gap-2 p-0" href="/shop/<?= $lang ?>/plum-brandy/210-465-aroma-pruna-slivovitz-1-1000-pg-special.html">
              <span class="d-flex align-items-center justify-content-center bg-body-tertiary rounded-circle" style="width: 60px; height: 60px">
                <img src="/x-assets/img/mega-menu/grocery/th05.png" width="60" alt="Image">
              </span>
              <span class="d-block animate-target fw-semibold text-nowrap ms-1"><?= t('cat7.cir6', 'New Plum!') ?></span>
            </a>
            <a class="nav-link align-items-center animate-underline gap-2 p-0" href="/<?= $lang ?>/">
              <span class="d-flex align-items-center justify-content-center bg-body-tertiary rounded-circle" style="width: 60px; height: 60px">
                <img src="/x-assets/img/mega-menu/grocery/th06.png" width="60" alt="Image">
              </span>
              <span class="d-block animate-target fw-semibold text-nowrap ms-1"><?= t('cat7.cir7', 'Coming soon') ?></span>
            </a>
          </div>
        </div>
      </div>
    </section>
<!-- @mf small 6 category end-->



<!-- @mf 3blocks-->

<!-- NADPIS S LOGEM @mfn-->
<section class="text-center my-5">
  <!-- horní text -->
  <p class="subtitle mb-0">
    <?= t('cat3.h1p', 'Food products') ?>
  </p>

  <!-- hlavní nadpis -->
  <h2 class="title fw-bold text-dark mb-0">
    <?= t('cat3.h1', 'Our Product Range') ?>
  </h2>

  <!-- oddělovač s logem -->
  <div class="d-flex align-items-center justify-content-center mt-0">
    <div class="flex-grow-1 mx-3 line"></div>
    <img src="/x-assets/img/zan-sign.png" alt="Logo" class="mx-2" style="height:40px;">
    <div class="flex-grow-1 mx-3 line"></div>
  </div>
</section>

      <!-- Featured categories that turns into carousel on screen < 992px (lg breackpoint) -->
      <section class="container pt-4 pb-5 mb-2 mb-sm-3 mb-lg-4 mb-xl-5">
        <div class="swiper" data-swiper='{
          "slidesPerView": 1,
          "spaceBetween": 24,
          "pagination": {
            "el": ".swiper-pagination",
            "clickable": true
          },
          "breakpoints": {
            "680": {
              "slidesPerView": 2
            },
            "992": {
              "slidesPerView": 3
            }
          }
        }'>
          <div class="swiper-wrapper">

            <!-- Category -->
            <div class="swiper-slide h-auto">
              <div class="position-relative d-flex justify-content-between align-items-center h-100 rounded-5 overflow-hidden ps-2 ps-xl-3" style="background:#E7F4FE; border:0px solid #49A3E4;">
                <div class="d-flex flex-column pt-4 px-3 pb-3">
                  <p class="fs-xs pb-2 mb-1">130+ <?= t('cat3.prod', 'Products') ?></p>
                  <h2 class="h5 mb-2 mb-xxl-3"><?= t('cat3.sor1', 'Aromas, Tinctures & Essential Oils') ?></h2>
                  <div class="nav">
                    <a class="nav-link animate-underline stretched-link text-body-emphasis text-nowrap px-0" href="/shop/<?= $lang ?>/10-aromata">
                      <span class="animate-target"><?= t('best.f1b', 'Shop now') ?></span>
                      <i class="ci-chevron-right fs-base ms-1"></i>
                    </a>
                  </div>
                </div>
                <div class="ratio w-100 align-self-end rtl-flip" style="max-width: 216px; --cz-aspect-ratio: calc(240 / 216 * 100%)">
                  <img src="/x-assets/img/home/grocery/featured/01.png" alt="Image">
                </div>
              </div>
            </div>

            <!-- Category -->
            <div class="swiper-slide h-auto">
              <div class="position-relative d-flex justify-content-between align-items-center h-100 rounded-5 overflow-hidden ps-2 ps-xl-3"style="background:#E7F4FE; border:0px solid #49A3E4;">
                <div class="d-flex flex-column pt-4 px-3 pb-3">
                  <p class="fs-xs pb-2 mb-1">10+ <?= t('cat3.prod', 'Products') ?></p>
                  <h2 class="h5 mb-2 mb-xxl-3"><?= t('cat3.sor2', 'Fruit Juices & Concentrates') ?></h2>
                  <div class="nav">
                    <a class="nav-link animate-underline stretched-link text-body-emphasis text-nowrap px-0" href="/shop/<?= $lang ?>/<?= t('url.contact', 'contact-us') ?>">
                      <span class="animate-target"><?= t('best.f1b', 'Shop now') ?></span>
                      <i class="ci-chevron-right fs-base ms-1"></i>
                    </a>
                  </div>
                </div>
                <div class="ratio w-100 align-self-end rtl-flip" style="max-width: 216px; --cz-aspect-ratio: calc(240 / 216 * 100%)">
                  <img src="/x-assets/img/home/grocery/featured/02.png" alt="Image">
                </div>
              </div>
            </div>

            <!-- Category -->
            <div class="swiper-slide h-auto">
              <div class="position-relative d-flex justify-content-between align-items-center h-100 rounded-5 overflow-hidden ps-2 ps-xl-3" style="background:#E7F4FE; border:0px solid #49A3E4;">
                <div class="d-flex flex-column pt-4 px-3 pb-3">
                  <p class="fs-xs pb-2 mb-1">90+ <?= t('cat3.prod', 'Products') ?></p>
                  <h2 class="h5 mb-2 mb-xxl-3"><?= t('cat3.sor3', 'Winemaking means and preparations') ?></h2>
                  <div class="nav">
                    <a class="nav-link animate-underline stretched-link text-body-emphasis text-nowrap px-0" href="/shop/<?= $lang ?>/11-vinarstvi">
                      <span class="animate-target"><?= t('best.f1b', 'Shop now') ?></span>
                      <i class="ci-chevron-right fs-base ms-1"></i>
                    </a>
                  </div>
                </div>
                <div class="ratio w-100 align-self-end rtl-flip" style="max-width: 216px; --cz-aspect-ratio: calc(240 / 216 * 100%)">
                  <img src="/x-assets/img/home/grocery/featured/03.png" alt="Image">
                </div>
              </div>
            </div>
          </div>
<!-- @mf 3blocks end-->




<!-- NADPIS S LOGEM @mfn-->
<section class="text-center my-5">
  <!-- horní text -->
  <p class="subtitle mb-0">
    <?= t('cat6.h1p', 'Flavoring of Spirits') ?>
  </p>

  <!-- hlavní nadpis -->
  <h2 class="title fw-bold text-dark mb-0">
    <?= t('cat6.h1', 'FLAVORS FOR BEVERAGES') ?>
  </h2>

  <!-- oddělovač s logem -->
  <div class="d-flex align-items-center justify-content-center mt-0">
    <div class="flex-grow-1 mx-3 line"></div>
    <img src="/x-assets/img/zan-sign.png" alt="Logo" class="mx-2" style="height:40px;">
    <div class="flex-grow-1 mx-3 line"></div>
  </div>
</section>



      <!-- Categories -->
      <section class="container py-5 my-2 my-sm-3 mb-md-2 mt-lg-4 my-xl-5">
        <div class="overflow-x-auto pt-xxl-3" data-simplebar data-simplebar-auto-hide="false">
          <div class="row flex-nowrap flex-md-wrap justify-content-md-center g-0 gap-4 gap-md-0">

            <!-- Category -->
            <div class="col col-md-4 col-lg-3 col-xl-2 mb-4">
              <div class="category-card w-100 text-center px-1 px-lg-2 px-xxl-3 mx-auto" style="min-width: 165px">
                <div class="category-card-body">
                  <a class="d-block text-decoration-none" href="/shop/<?= $lang ?>/29-spirits">
                    <div class="bg-body-tertiary rounded-pill mb-3 mx-auto" style="max-width: 164px">
                      <div class="ratio ratio-1x1">
                        <img src="/x-assets/img/home/flavor/categories/01-spirits.png" class="rounded-pill" alt="">
                      </div>
                    </div>
                    <h3 class="category-card-title h6 text-truncate"><?= t('cat6.f1', 'Spirits') ?></h3>
                  </a>
                  <ul class="category-card-list nav w-100 flex-column gap-1 pt-3">
                    <li class="w-100">
                      <a class="nav-link justify-content-center min-w-0 w-100 fw-normal hover-effect-underline p-0" href="/shop/<?= $lang ?>/113-whisky-bourbon">
                        <h4 class="text-truncate fw-normal m-0 d-inline" style="font-size: inherit;"><?= t('cat6.f11', 'Whisky') ?></h4>
                      </a>
                    </li>
                    <li class="w-100">
                      <a class="nav-link justify-content-center min-w-0 w-100 fw-normal hover-effect-underline p-0" href="/shop/<?= $lang ?>/115-gin">
                        <h4 class="text-truncate fw-normal m-0 d-inline" style="font-size: inherit;"><?= t('cat6.f12', 'Gin') ?></h4>
                      </a>
                    </li>
                    <li class="w-100">
                      <a class="nav-link justify-content-center min-w-0 w-100 fw-normal hover-effect-underline p-0" href="/shop/<?= $lang ?>/112-rum">
                        <h4 class="text-truncate fw-normal m-0 d-inline" style="font-size: inherit;"><?= t('cat6.f13', 'Rum') ?></h4>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Category -->
            <div class="col col-md-4 col-lg-3 col-xl-2 mb-4">
              <div class="category-card w-100 text-center px-1 px-lg-2 px-xxl-3 mx-auto" style="min-width: 165px">
                <div class="category-card-body">
                  <a class="d-block text-decoration-none" href="/shop/<?= $lang ?>/30-fruit-distillates">
                    <div class="bg-body-tertiary rounded-pill mb-3 mx-auto" style="max-width: 164px">
                      <div class="ratio ratio-1x1">
                        <img src="/x-assets/img/home/flavor/categories/02-brandy.png" class="rounded-pill" alt="">
                      </div>
                    </div>
                    <h3 class="category-card-title h6 text-truncate"><?= t('cat6.f2', 'Fruit Distillates') ?></h3>
                  </a>
                  <ul class="category-card-list nav w-100 flex-column gap-1 pt-3">
                    <li class="w-100">
                      <a class="nav-link justify-content-center min-w-0 w-100 fw-normal hover-effect-underline p-0" href="/shop/<?= $lang ?>/89-apricot-brandy">
                        <h4 class="text-truncate fw-normal m-0 d-inline" style="font-size: inherit;"><?= t('cat6.f21', 'Apricot Brandy') ?></h4>
                      </a>
                    </li>
                    <li class="w-100">
                      <a class="nav-link justify-content-center min-w-0 w-100 fw-normal hover-effect-underline p-0" href="/shop/<?= $lang ?>/81-plum-brandy">
                        <h4 class="text-truncate fw-normal m-0 d-inline" style="font-size: inherit;"><?= t('cat6.f22', 'Plum Brandy') ?></h4>
                      </a>
                    </li>
                    <li class="w-100">
                      <a class="nav-link justify-content-center min-w-0 w-100 fw-normal hover-effect-underline p-0" href="/shop/<?= $lang ?>/80-pear-brandy">
                        <h4 class="text-truncate fw-normal m-0 d-inline" style="font-size: inherit;"><?= t('cat6.f23', 'Pear Brandy') ?></h4>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Category -->
            <div class="col col-md-4 col-lg-3 col-xl-2 mb-4">
              <div class="category-card w-100 text-center px-1 px-lg-2 px-xxl-3 mx-auto" style="min-width: 165px">
                <div class="category-card-body">
                  <a class="d-block text-decoration-none" href="/shop/<?= $lang ?>/31-liqueurs">
                    <div class="bg-body-tertiary rounded-pill mb-3 mx-auto" style="max-width: 164px">
                      <div class="ratio ratio-1x1">
                        <img src="/x-assets/img/home/flavor/categories/03-liqueurs.png" class="rounded-pill" alt="">
                      </div>
                    </div>
                    <h3 class="category-card-title h6 text-truncate"><?= t('cat6.f3', 'Liqueurs') ?></h3>
                  </a>
                  <ul class="category-card-list nav w-100 flex-column gap-1 pt-3">
                    <li class="w-100">
                      <a class="nav-link justify-content-center min-w-0 w-100 fw-normal hover-effect-underline p-0" href="/shop/<?= $lang ?>/142-aperol-liqueur">
                        <h4 class="text-truncate fw-normal m-0 d-inline" style="font-size: inherit;"><?= t('cat6.f31', 'Aperol') ?></h4>
                      </a>
                    </li>
                    <li class="w-100">
                      <a class="nav-link justify-content-center min-w-0 w-100 fw-normal hover-effect-underline p-0" href="/shop/<?= $lang ?>/122-fruit-liqueurs">
                        <h4 class="text-truncate fw-normal m-0 d-inline" style="font-size: inherit;"><?= t('cat6.f32', 'Fruit') ?></h4>
                      </a>
                    </li>
                    <li class="w-100">
                      <a class="nav-link justify-content-center min-w-0 w-100 fw-normal hover-effect-underline p-0" href="/shop/<?= $lang ?>/121-herbal-liqueurs">
                        <h4 class="text-truncate fw-normal m-0 d-inline" style="font-size: inherit;"><?= t('cat6.f33', 'Herbal') ?></h4>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Category -->
            <div class="col col-md-4 col-lg-3 col-xl-2 mb-4">
              <div class="category-card w-100 text-center px-1 px-lg-2 px-xxl-3 mx-auto" style="min-width: 165px">
                <div class="category-card-body">
                  <a class="d-block text-decoration-none" href="/shop/<?= $lang ?>/32-wine-beverages">
                    <div class="bg-body-tertiary rounded-pill mb-3 mx-auto" style="max-width: 164px">
                      <div class="ratio ratio-1x1">
                        <img src="/x-assets/img/home/flavor/categories/04-wine.png" class="rounded-pill" alt="">
                      </div>
                    </div>
                    <h3 class="category-card-title h6 text-truncate"><?= t('cat6.f4', 'Wine Beverages') ?></h3>
                  </a>
                  <ul class="category-card-list nav w-100 flex-column gap-1 pt-3">
                    <li class="w-100">
                      <a class="nav-link justify-content-center min-w-0 w-100 fw-normal hover-effect-underline p-0" href="/shop/<?= $lang ?>/98-muscat-ottonel">
                        <h4 class="text-truncate fw-normal m-0 d-inline" style="font-size: inherit;"><?= t('cat6.f41', 'Muscat Ottonel') ?></h4>
                      </a>
                    </li>
                    <li class="w-100">
                      <a class="nav-link justify-content-center min-w-0 w-100 fw-normal hover-effect-underline p-0" href="/shop/<?= $lang ?>/103-mueller-thurgau">
                        <h4 class="text-truncate fw-normal m-0 d-inline" style="font-size: inherit;"><?= t('cat6.f42', 'Müller Thurgau') ?></h4>
                      </a>
                    </li>
                    <li class="w-100">
                      <a class="nav-link justify-content-center min-w-0 w-100 fw-normal hover-effect-underline p-0" href="/shop/<?= $lang ?>/97-gewurztraminer">
                        <h4 class="text-truncate fw-normal m-0 d-inline" style="font-size: inherit;"><?= t('cat6.f43', 'Gewürztraminer') ?></h4>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Category -->
            <div class="col col-md-4 col-lg-3 col-xl-2 mb-4">
              <div class="category-card w-100 text-center px-1 px-lg-2 px-xxl-3 mx-auto" style="min-width: 165px">
                <div class="category-card-body">
                  <a class="d-block text-decoration-none" href="/shop/<?= $lang ?>/33-beer">
                    <div class="bg-body-tertiary rounded-pill mb-3 mx-auto" style="max-width: 164px">
                      <div class="ratio ratio-1x1">
                        <img src="/x-assets/img/home/flavor/categories/05-beer.png" class="rounded-pill" alt="">
                      </div>
                    </div>
                    <h3 class="category-card-title h6 text-truncate"><?= t('cat6.f5', 'Beer') ?></h3>
                  </a>
                  <ul class="category-card-list nav w-100 flex-column gap-1 pt-3">
                    <li class="w-100">
                      <a class="nav-link justify-content-center min-w-0 w-100 fw-normal hover-effect-underline p-0" href="/shop/<?= $lang ?>/baking-confectionery/15-flavour-sour-cherry-12000-pg.html">
                        <h4 class="text-truncate fw-normal m-0 d-inline" style="font-size: inherit;"><?= t('cat6.f51', 'Sour Cherry') ?></h4>
                      </a>
                    </li>
                    <li class="w-100">
                      <a class="nav-link justify-content-center min-w-0 w-100 fw-normal hover-effect-underline p-0" href="/shop/<?= $lang ?>/baking-confectionery/4-flavour-pear-11000-pg.html">
                        <h4 class="text-truncate fw-normal m-0 d-inline" style="font-size: inherit;"><?= t('cat6.f52', 'Pear') ?></h4>
                      </a>
                    </li>
                    <li class="w-100">
                      <a class="nav-link justify-content-center min-w-0 w-100 fw-normal hover-effect-underline p-0" href="/shop/<?= $lang ?>/baking-confectionery/1-flavour-pineapple-12000-pg.html">
                        <h4 class="text-truncate fw-normal m-0 d-inline" style="font-size: inherit;"><?= t('cat6.f53', 'Pineapple') ?></h4>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Category -->
            <div class="col col-md-4 col-lg-3 col-xl-2 mb-4">
              <div class="category-card w-100 text-center px-1 px-lg-2 px-xxl-3 mx-auto" style="min-width: 165px">
                <div class="category-card-body">
                  <a class="d-block text-decoration-none" href="/shop/<?= $lang ?>/34-mead">
                    <div class="bg-body-tertiary rounded-pill mb-3 mx-auto" style="max-width: 164px">
                      <div class="ratio ratio-1x1">
                        <img src="/x-assets/img/home/flavor/categories/06-mead.png" class="rounded-pill" alt="">
                      </div>
                    </div>
                    <h3 class="category-card-title h6 text-truncate"><?= t('cat6.f6', 'Mead') ?></h3>
                  </a>
                  <ul class="category-card-list nav w-100 flex-column gap-1 pt-3">
                    <li class="w-100">
                      <a class="nav-link justify-content-center min-w-0 w-100 fw-normal hover-effect-underline p-0" href="/shop/<?= $lang ?>/mead/9-flavour-honey-to-mead-11000-pg.html">
                        <h4 class="text-truncate fw-normal m-0 d-inline" style="font-size: inherit;"><?= t('cat6.f61', 'Honey') ?></h4>
                      </a>
                    </li>
                    <li class="w-100">
                      <a class="nav-link justify-content-center min-w-0 w-100 fw-normal hover-effect-underline p-0" href="/shop/<?= $lang ?>/mead/214-flavour-amaro-per-miele-to-mead-1-100.html">
                        <h4 class="text-truncate fw-normal m-0 d-inline" style="font-size: inherit;"><?= t('cat6.f62', 'Amaro for Mead') ?></h4>
                      </a>
                    </li>
                    <!-- 
                    <li class="w-100">
                      <a class="nav-link justify-content-center min-w-0 w-100 fw-normal hover-effect-underline p-0" href="shop-catalog-furniture.html">
                        <span class="text-truncate">Dining tables</span>
                      </a>
                    </li>
                    -->
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>


<!-- NADPIS S LOGEM @mfn-->
<section class="text-center my-5">
  <!-- horní text -->
  <p class="subtitle mb-0">
    <?= t('best.fh3p', 'Customer Selection') ?>
  </p>

  <!-- hlavní nadpis -->
  <h3 class="title fw-bold text-dark mb-0">
    <?= t('best.fh3', 'BEST-SELLING FLAVORS') ?>
  </h3>

  <!-- oddělovač s logem -->
  <div class="d-flex align-items-center justify-content-center mt-0">
    <div class="flex-grow-1 mx-3 line"></div>
    <img src="/x-assets/img/zan-sign.png" alt="Logo" class="mx-2" style="height:40px;">
    <div class="flex-grow-1 mx-3 line"></div>
  </div>
</section>



      <!-- Popular products carousel -->
      <section class="container pb-5 mt-md-n2 mb-2 mb-sm-3 mb-md-4 mb-xl-5">

        <!-- Heading -->
        <div class="d-flex align-items-center justify-content-between border-bottom pb-3 pb-md-4">
          <h4 class="h3 mb-0"><?= t('best.f0', 'Popular products') ?></h4>
          <div class="nav ms-3">
            <a class="nav-link animate-underline px-0 py-2" href="/shop/<?= $lang ?>/133-best-sellers">
              <span class="animate-target"><?= t('best.f0b', 'View all') ?></span>
              <i class="ci-chevron-right fs-base ms-1"></i>
            </a>
          </div>
        </div>

        <!-- Product carousel -->
        <div class="position-relative pb-xxl-3">

          <!-- External slider prev/next buttons visible on screens > 500px wide (sm breakpoint) -->
          <button type="button" class="popular-prev btn btn-icon btn-outline-secondary bg-body rounded-circle animate-slide-start position-absolute top-50 start-0 z-2 translate-middle mt-n5 d-none d-sm-inline-flex" aria-label="Prev">
            <i class="ci-chevron-left fs-lg animate-target"></i>
          </button>
          <button type="button" class="popular-next btn btn-icon btn-outline-secondary bg-body rounded-circle animate-slide-end position-absolute top-50 start-100 z-2 translate-middle mt-n5 d-none d-sm-inline-flex" aria-label="Next">
            <i class="ci-chevron-right fs-lg animate-target"></i>
          </button>

          <!-- Slider -->
          <div class="swiper pt-3 pt-sm-4" data-swiper='{
            "slidesPerView": 2,
            "spaceBetween": 24,
            "loop": true,
            "navigation": {
              "prevEl": ".popular-prev",
              "nextEl": ".popular-next"
            },
            "breakpoints": {
              "768": {
                "slidesPerView": 3
              },
              "992": {
                "slidesPerView": 4
              }
            }
          }'>
            <div class="swiper-wrapper">

              <!-- Item -->
              <div class="swiper-slide">
                <div class="animate-underline">
                  <a class="hover-effect-opacity ratio ratio-1x1 d-block mb-3" href="/shop/<?= $lang ?>/apricot-brandy/108-26-flavour-apricot-1400.html">
                    <img src="/x-assets/img/products/01-apricot.png" class="hover-effect-target opacity-100" alt="Product">
                    <img src="/x-assets/img/products/01-apricot.png" class="position-absolute top-0 start-0 hover-effect-target opacity-0 rounded-4" alt="Room">
                  </a>
                  <div class="d-flex gap-2 mb-3">
                    <input type="radio" class="btn-check" name="colors-4" id="color-4-1" checked>
                    <label for="color-4-1" class="btn btn-color fs-base" style="color: #fff3d4">
                      <span class="visually-hidden">Yellowish</span>
                    </label>
                    <!-- 
                    <input type="radio" class="btn-check" name="colors-4" id="color-4-2">
                    <label for="color-4-2" class="btn btn-color fs-base" style="color: #bdc5da">
                      <span class="visually-hidden">Light gray</span>
                    </label>
                    <input type="radio" class="btn-check" name="colors-4" id="color-4-3">
                    <label for="color-4-3" class="btn btn-color fs-base" style="color: #526f99">
                      <span class="visually-hidden">Bluish gray</span>
                    </label>
                    -->
                  </div>
                  <h5 class="mb-2">
                    <a class="d-block fs-sm fw-medium text-truncate" href="/shop/<?= $lang ?>/apricot-brandy/108-26-flavour-apricot-1400.html">
                      <span class="h5 animate-target"><?= t('best.f1', 'Flavour APRICOT') ?></span>
                    </a>
                  </h5>
                  <div class="h6">1:400</div>
                  <div class="d-flex gap-2">
                    <!--
                    <button type="button" class="btn btn-dark w-100 rounded-pill px-3">Shop now</button>
                    -->
                    <a href="/shop/<?= $lang ?>/apricot-brandy/108-26-flavour-apricot-1400.html" class="btn btn-dark w-100 rounded-pill px-3"><?= t('best.f1b', 'Shop now') ?></a>

                    <button type="button" class="btn btn-icon btn-secondary rounded-circle animate-pulse" aria-label="Add to wishlist">
                      <i class="ci-heart fs-base animate-target"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Item -->
              <div class="swiper-slide">
                <div class="animate-underline">
                  <a class="hover-effect-opacity ratio ratio-1x1 d-block mb-3" href="/shop/<?= $lang ?>/plum-brandy/106-113-flavour-plum-1400.html">
                    <img src="/x-assets/img/products/02-plum.png" class="hover-effect-target opacity-100" alt="Product">
                    <img src="/x-assets/img/products/02-plum.png" class="position-absolute top-0 start-0 hover-effect-target opacity-0 rounded-4" alt="Room">
                  </a>
                  <div class="d-flex gap-2 mb-3">
                    <input type="radio" class="btn-check" name="colors-5" id="color-5-1" checked>
                    <label for="color-5-1" class="btn btn-color fs-base" style="color: #f7e56d">
                      <span class="visually-hidden">Yellow</span>
                    </label>
                    <!--
                    <input type="radio" class="btn-check" name="colors-5" id="color-5-2">
                    <label for="color-5-2" class="btn btn-color fs-base" style="color: #777d7E">
                      <span class="visually-hidden">Gray</span>
                    </label>
                    -->
                  </div>
                  <h4 class="mb-2">
                    <a class="d-block fs-sm fw-medium text-truncate" href="/shop/<?= $lang ?>/plum-brandy/106-113-flavour-plum-1400.html">
                      <span class="h5 animate-target"><?= t('best.f2', 'Flavour PLUM') ?></span>
                    </a>
                  </h4>
                  <div class="h6">1:400</div>
                  <div class="d-flex gap-2">
                    <a href="/shop/<?= $lang ?>/plum-brandy/106-113-flavour-plum-1400.html" class="btn btn-dark w-100 rounded-pill px-3"><?= t('best.f2b', 'Shop now') ?></a>

                    <button type="button" class="btn btn-icon btn-secondary rounded-circle animate-pulse" aria-label="Add to wishlist">
                      <i class="ci-heart fs-base animate-target"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Item -->
              <div class="swiper-slide">
                <div class="animate-underline">
                  <a class="hover-effect-opacity ratio ratio-1x1 d-block mb-3" href="/shop/<?= $lang ?>/pear-brandy/32-109-flavour-pear-1400.html">
                    <!-- 
                    <div class="position-absolute top-0 start-0 z-2 mt-2 mt-sm-3 ms-2 ms-sm-3">
                      
                      <span class="badge text-bg-danger">-13%</span>
                    </div>
                    -->
                    <img src="/x-assets/img/products/03-pear.png" class="hover-effect-target opacity-100" alt="Product">
                    <img src="/x-assets/img/products/03-pear.png" class="position-absolute top-0 start-0 hover-effect-target opacity-0 rounded-4" alt="Room">
                  </a>
                  <div class="d-flex gap-2 mb-3">
                    <input type="radio" class="btn-check" name="colors-6" id="color-6-1" checked>
                    <label for="color-6-1" class="btn btn-color fs-base" style="color: #fcfcfc">
                      <span class="visually-hidden">White</span>
                    </label>
                    <!--
                    <input type="radio" class="btn-check" name="colors-6" id="color-6-2">
                    <label for="color-6-2" class="btn btn-color fs-base" style="color: #d65c46">
                      <span class="visually-hidden">Terracotta</span>
                    </label>
                    <input type="radio" class="btn-check" name="colors-6" id="color-6-3">
                    <label for="color-6-3" class="btn btn-color fs-base" style="color: #e0e5eb">
                      <span class="visually-hidden">White</span>
                    </label>
                    -->
                  </div>
                  <h4 class="mb-2">
                    <a class="d-block fs-sm fw-medium text-truncate" href="/shop/<?= $lang ?>/pear-brandy/32-109-flavour-pear-1400.html">
                      <span class="h5 animate-target"><?= t('best.f3', 'Flavour PEAR') ?></span>
                    </a>
                  </h4>
                  <!-- sleva
                  <div class="h6">$140.00 <del class="fs-sm fw-normal text-body-tertiary">$160.00</del></div>
                  -->
                  <div class="h6">1:400</div>
                  <div class="d-flex gap-2">
                    <a href="/shop/<?= $lang ?>/pear-brandy/32-109-flavour-pear-1400.html" class="btn btn-dark w-100 rounded-pill px-3"><?= t('best.f3b', 'Shop now') ?></a>

                    <button type="button" class="btn btn-icon btn-secondary rounded-circle animate-pulse" aria-label="Add to wishlist">
                      <i class="ci-heart fs-base animate-target"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Item -->
              <div class="swiper-slide">
                <div class="animate-underline">
                  <a class="hover-effect-opacity ratio ratio-1x1 d-block mb-3" href="/shop/<?= $lang ?>/sour-cherry-brandy/105-28-flavour-sour-cherry-1400.html">
                    <img src="/x-assets/img/products/04-sour-cherry.png" class="hover-effect-target opacity-100" alt="Product">
                    <img src="/x-assets/img/products/04-sour-cherry.png" class="position-absolute top-0 start-0 hover-effect-target opacity-0 rounded-4" alt="Room">
                  </a>
                  <div class="d-flex gap-2 mb-3">
                    <input type="radio" class="btn-check" name="colors-8" id="color-8-1" checked>
                    <label for="color-8-1" class="btn btn-color fs-base" style="color: #f55151">
                      <span class="visually-hidden">Red</span>
                    </label>
                    <!--
                    <input type="radio" class="btn-check" name="colors-8" id="color-8-2">
                    <label for="color-8-2" class="btn btn-color fs-base" style="color: #34598f">
                      <span class="visually-hidden">Blue</span>
                    </label>
                    -->
                  </div>
                  <h4 class="mb-2">
                    <a class="d-block fs-sm fw-medium text-truncate" href="/shop/<?= $lang ?>/sour-cherry-brandy/105-28-flavour-sour-cherry-1400.html">
                      <span class="h5 animate-target"><?= t('best.f4', 'Flavour SOUR CHERRY') ?></span>
                    </a>
                  </h4>
                  <div class="h6">1:400</div>
                  <div class="d-flex gap-2">
                    <a href="/shop/<?= $lang ?>/sour-cherry-brandy/105-28-flavour-sour-cherry-1400.html" class="btn btn-dark w-100 rounded-pill px-3"><?= t('best.f4b', 'Shop now') ?></a>

                    <button type="button" class="btn btn-icon btn-secondary rounded-circle animate-pulse" aria-label="Add to wishlist">
                      <i class="ci-heart fs-base animate-target"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Item -->
              <div class="swiper-slide">
                <div class="animate-underline">
                  <a class="hover-effect-opacity ratio ratio-1x1 d-block mb-3" href="/shop/<?= $lang ?>/peach-brandy/39-112-flavour-peach-1400.html">
                    <img src="/x-assets/img/products/05-peach.png" class="hover-effect-target opacity-100" alt="Product">
                    <img src="/x-assets/img/products/05-peach.png" class="position-absolute top-0 start-0 hover-effect-target opacity-0 rounded-4" alt="Room">
                  </a>
                  <div class="d-flex gap-2 mb-3">
                    <input type="radio" class="btn-check" name="colors-2" id="color-2-1" checked>
                    <label for="color-2-1" class="btn btn-color fs-base" style="color: #fff3d4">
                      <span class="visually-hidden">Yellowish</span>
                    </label>
                    <!-- 
                    <input type="radio" class="btn-check" name="colors-2" id="color-2-2">
                    <label for="color-2-2" class="btn btn-color fs-base" style="color: #373b42">
                      <span class="visually-hidden">Dark gray</span>
                    </label>
                    <input type="radio" class="btn-check" name="colors-2" id="color-2-3">
                    <label for="color-2-3" class="btn btn-color fs-base" style="color: #216aae">
                      <span class="visually-hidden">Blue</span>
                    </label>
                    <input type="radio" class="btn-check" name="colors-2" id="color-2-4">
                    <label for="color-2-4" class="btn btn-color fs-base" style="color: #187c1c">
                      <span class="visually-hidden">Green</span>
                    </label>
                    -->
                  </div>
                  <h4 class="mb-2">
                    <a class="d-block fs-sm fw-medium text-truncate" href="/shop/<?= $lang ?>/peach-brandy/39-112-flavour-peach-1400.html">
                      <span class="h5 animate-target"><?= t('best.f5', 'Flavour PEACH') ?></span>
                    </a>
                  </h4>
                  <div class="h6">1:400</div>
                  <div class="d-flex gap-2">
                    <a href="/shop/<?= $lang ?>/peach-brandy/39-112-flavour-peach-1400.html" class="btn btn-dark w-100 rounded-pill px-3"><?= t('best.f5b', 'Shop now') ?></a>

                    <button type="button" class="btn btn-icon btn-secondary rounded-circle animate-pulse" aria-label="Add to wishlist">
                      <i class="ci-heart fs-base animate-target"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Item -->
              <div class="swiper-slide">
                <div class="animate-underline">
                  <a class="hover-effect-opacity ratio ratio-1x1 d-block mb-3" href="/shop/<?= $lang ?>/apple-brandy/42-flavour-apple-1400.html">
                    <img src="/x-assets/img/products/06-apple.png" class="hover-effect-target opacity-100" alt="Product">
                    <img src="/x-assets/img/products/06-apple.png" class="position-absolute top-0 start-0 hover-effect-target opacity-0 rounded-4" alt="Room">
                  </a>
                  <div class="d-flex gap-2 mb-3">
                    <input type="radio" class="btn-check" name="colors-7" id="color-7-1" checked>
                    <label for="color-7-1" class="btn btn-color fs-base" style="color: #fff3d4">
                      <span class="visually-hidden">Yellowish</span>
                    </label>
                    <!-- 
                    <input type="radio" class="btn-check" name="colors-7" id="color-7-2">
                    <label for="color-7-2" class="btn btn-color fs-base" style="color: #c1c3b8">
                      <span class="visually-hidden">Light gray</span>
                    </label>
                    -->
                  </div>
                  <h4 class="mb-2">
                    <a class="d-block fs-sm fw-medium text-truncate" href="/shop/<?= $lang ?>/apple-brandy/42-flavour-apple-1400.html">
                      <span class="h5 animate-target"><?= t('best.f6', 'Flavour APPLE') ?></span>
                    </a>
                  </h4>
                  <div class="h6">1:400</div>
                  <div class="d-flex gap-2">
                    <a href="/shop/<?= $lang ?>/apple-brandy/42-flavour-apple-1400.html" class="btn btn-dark w-100 rounded-pill px-3"><?= t('best.f6b', 'Shop now') ?></a>

                    <button type="button" class="btn btn-icon btn-secondary rounded-circle animate-pulse" aria-label="Add to wishlist">
                      <i class="ci-heart fs-base animate-target"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- External slider prev/next buttons visible on screens < 500px wide (sm breakpoint) -->
        <div class="d-flex justify-content-center gap-2 mt-1 pt-4 d-sm-none">
          <button type="button" class="popular-prev btn btn-icon btn-outline-secondary bg-body rounded-circle animate-slide-start me-1" aria-label="Prev">
            <i class="ci-chevron-left fs-lg animate-target"></i>
          </button>
          <button type="button" class="popular-next btn btn-icon btn-outline-secondary bg-body rounded-circle animate-slide-end" aria-label="Next">
            <i class="ci-chevron-right fs-lg animate-target"></i>
          </button>
        </div>
      </section>


<!-- NADPIS S LOGEM @mfn-->
<section class="text-center my-5">
  <!-- horní text -->
  <p class="subtitle mb-0">
    <?= t('eno.h2p', 'Selection of Winemakers') ?>
  </p>

  <!-- hlavní nadpis -->
  <h2 class="title fw-bold text-dark mb-0">
    <?= t('eno.h2', 'ENOLOGICAL-WINE AGENTS') ?>
  </h2>

  <!-- oddělovač s logem -->
  <div class="d-flex align-items-center justify-content-center mt-0">
    <div class="flex-grow-1 mx-3 line"></div>
    <img src="/x-assets/img/zan-sign.png" alt="Logo" class="mx-2" style="height:40px;">
    <div class="flex-grow-1 mx-3 line"></div>
  </div>
</section>


<!-- enologie grid -->
<section class="container pb-5 mb-2 mb-sm-3 mb-lg-4 mb-xl-5">
  <style>
    .product-card { position: relative; }
    .product-card .product-overlay {
      position: absolute;
      inset: 0;
      display: grid;
      place-items: center;
      padding: 1rem;
      text-align: center;
      color: #fff;
      background-color: rgba(0, 0, 0, 0.55);
      border-radius: inherit;
      opacity: 0;
      transition: opacity .25s ease;
      pointer-events: none;
      z-index: 1;
    }
    .product-card:hover .product-overlay,
    .product-card:focus-within .product-overlay { opacity: 1; }
        .product-overlay .overlay-text {
      font-size: .95rem;
      line-height: 1.35;
      font-weight: 600;
      text-shadow: 0 1px 2px rgba(0,0,0,.4);
      display: -webkit-box;
      -webkit-line-clamp: 11;
      -webkit-box-orient: vertical;
      overflow: hidden;
      max-height: calc(1.35em * 11);
    }
  </style>


  <h2 class="text-center pb-2 pb-sm-3"></h2>

  <!-- Nav pills -->
  <div class="row g-0 overflow-x-auto pb-2 pb-sm-3 mb-3">
    <div class="col-auto pb-1 pb-sm-0 mx-auto">
      <ul class="nav nav-pills flex-nowrap text-nowrap" id="enoFilters">
        <li class="nav-item">
          <h3 class="visually-hidden"><?= t('eno.h3fer', 'Fermentation') ?></h3>
          <h4 class="mb-2">
          <a class="nav-link active" href="" role="button" data-filter="yeast" aria-current="page"><?= t('eno.yea', 'Yeast') ?></a>
        </h4></li>
        <li class="nav-item">
          <h3 class="visually-hidden"><?= t('eno.h3fer', 'Fermentation') ?></h3>
          <h4 class="mb-2">
          <a class="nav-link" href="" role="button" data-filter="nutrients"><?= t('eno.nut', 'Nutrients') ?></a>
        </h4></li>
        <li class="nav-item">
          <h3 class="visually-hidden"><?= t('eno.h3fer', 'Fermentation') ?></h3>
          <h4 class="mb-2">
          <a class="nav-link" href="" role="button" data-filter="enzymes"><?= t('eno.enz', 'Enzymes') ?></a>
        </h4></li>
        <li class="nav-item">
          <h3 class="visually-hidden"><?= t('eno.h3sta', 'Stabilization') ?></h3>
          <h4 class="mb-2">
          <a class="nav-link" href="" role="button" data-filter="tannins"><?= t('eno.tan', 'Tannins') ?></a>
        </h4></li>
        <li class="nav-item">
          <h3 class="visually-hidden"><?= t('eno.h3cla', 'Clarification') ?></h3>
          <h4 class="mb-2">
          <a class="nav-link" href="" role="button" data-filter="bentonites"><?= t('eno.ben', 'Bentonites') ?></a>
        </h4></li>
      </ul>
    </div>
  </div>

  <!-- Products grid -->
  <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 gy-4 gy-md-5 pb-xxl-3" id="enoGrid">

    <!-- ===================== YEAST (ORIGINÁL 8 ks, BEZE ZMĚNY) ===================== -->

    <!-- Item 1 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="yeast">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <!--
          <span class="badge text-bg-danger position-absolute top-0 start-0 z-2 mt-2 mt-sm-3 ms-2 ms-sm-3">Sale</span>
          -->
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/yeast/133-fervens-slc-cerevisiae.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/SLC.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.y1', 'Saccharomyces cerevisiae for pure fermentation. Very active, for every kind of wine. Safe fermentation for white, red and rosé wines. Suitable and often used for fruit juices. It is a basic type of yeast with versatile use.') ?></div>
          </div>
        </div>
        <h5 class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/yeast/133-fervens-slc-cerevisiae.html">
            <span class="text-truncate">Fervens™ SLC (Cerevisiae)</span>
          </a>
        </h5>
      </div>
    </div>

    <!-- Item 2 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="yeast">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/yeast/131-wine-yeast-fervens-fragrance.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/fragrance.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.y2', 'For the fermentation of white and rosé wines in order to achieve the development of aromas that go from tropical to citrus notes. Thanks to a very low production of riboflavin, it improves wine longevity in the bottle.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/yeast/131-wine-yeast-fervens-fragrance.html">
            <span class="text-truncate">Fervens™ Fragrance</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item 3 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="yeast">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/yeast/130-fervens-emothion.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/Fervens EmoTHIOn.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.y3', 'An yeast made to enhance the aromatic potential of grapes rich in thiols. A new yeast made to enhance the aromatic potential of thiols grapes. Fervens EmoThion, is a hybrid strain that is unique thanks to its capacity to release aromatic thiols even during low temperature fermentations (14°C).') ?>
            </div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/yeast/130-fervens-emothion.html">
            <span class="text-truncate">Fervens™ EmoTHIOn</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item 4 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="yeast">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/yeast/134-lalvin-71b-yseo.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/Lalvin 71B YSEO.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.y4', 'The largest producer of aromatic esters (isoamyl acetate) in the world. For young white and red wines and for new wines. Yeast suitable and often used for cider. This is a higher class of yeast. Lalvin 71B® is recommended for the production of Premium wines with pronounced fruit aroma. Through the production of stable ester compounds, the wines stay alive and fresh for a long time.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/yeast/134-lalvin-71b-yseo.html">
            <span class="text-truncate">Lalvin 71B YSEO®</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item 5 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="yeast">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/yeast/137-wine-yeast-lalvin-r2.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/Lalvin R2.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.y5', 'Lalvin R2™ was isolated in the Sauternes region of Bordeaux by Brian Croser of South Australia. It has excellent cold temperature properties and will ferment as low as 5°C. Lalvin R2™ was isolated in the Sauternes region of Bordeaux by Brian Croser of South Australia. It has excellent cold temperature properties and will ferment as low as 5°C.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/yeast/137-wine-yeast-lalvin-r2.html">
            <span class="text-truncate">LALVIN R2™</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item 6 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="yeast">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/yeast/138-lalvin-r7.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/lalvin r7.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.y6', 'Ready and quick fermentation even when arrested. No special activation treatments are required. Very high alcohol yield. This strain is characterized by the rapid start-up of the fermentation process, even in the higher presence of alcohol and sugar. The ability to produce alcohol is extremely high (up to 19 degrees).') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/yeast/138-lalvin-r7.html">
            <span class="text-truncate">Lalvin R7®</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item 7 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="yeast">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <!--
          <span class="badge text-bg-danger position-absolute top-0 start-0 z-2 mt-2 mt-sm-3 ms-2 ms-sm-3">-17%</span>
          -->
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/yeast/139-lalvin-s6u.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/Lalvin S6U.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.y7', 'For the characterization of white and red wines. Exaltation of spicy notes, high production of glycerol, marked cryophilia. Indicated in late harvests. This strain is able to develop 1.2 g/l more glycerine during fermentation than other yeast cultures. They are therefore suitable for optimum extract increase in wines (musts) with low natural extract. It ensures a good alcohol level (14°C).') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/yeast/139-lalvin-s6u.html">
            <span class="text-truncate">Lalvin S6U™</span>
          </a>
        </div>
        <div class="h6 mb-2"><del class="fs-sm fw-normal text-body-tertiary"></del></div>
      </div>
    </div>

    <!-- Item 8 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="yeast">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/yeast/136-wine-yeast-lalvin-cy3079-yseo.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/lalvin cy3079.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.y8', 'For the processing of great white wines, especially from Chardonnay vines. Suitable for fermentation in barrique. This strain greatly enhances the primary aromas in Chardonnay varieties, lending them considerable length and great complexity. During autolysis, it releases complex aromas that complement the floral and fruity characteristics typical of fermentation.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/yeast/136-wine-yeast-lalvin-cy3079-yseo.html">
            <span class="text-truncate">Lalvin CY3079 YSEO®</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- ===================== NUTRIENTS (8 ks, ZKOPÍROVANÉ) ===================== -->
    <!-- Item N1 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="nutrients">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <!--
          <span class="badge text-bg-danger position-absolute top-0 start-0 z-2 mt-2 mt-sm-3 ms-2 ms-sm-3">Sale</span>
          -->
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/nutrients/146-superattivante-dc.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/Superattivante.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.n1', 'Complex nutrient based on inorganic nitrogen and thiamine. Nourishment complex based on ammonium salts and vitamin B1. The ideal time for addition of Superattivante DC is with the yeast inoculum. It is ideal for use in musts deficient in nutritional factors (FAN<80-100 mg/l N) as a result of excessive clarification, in must where microbiological contamination is high, where overripe grapes are used, etc.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/nutrients/146-superattivante-dc.html">
            <span class="text-truncate">Superattivante DC</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Item N2 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="nutrients">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/nutrients/143-poliattivante-f.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/Poliattivante-F.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.n2', 'Complete nutrient containing diammonium phosphate, thiamine and cellulose. When added at the beginning of the fermentation it compensates for eventual nutritional deficiencies. The addition of Poliattivante F to the must at the beginning of the fermentation compensates for eventual nutritional deficiencies.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/nutrients/143-poliattivante-f.html">
            <span class="text-truncate">Poliattivante F</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item N3 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="nutrients">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/nutrients/142-lifty-sense.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/lifty-sense.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.n3', 'Improver to be used at the beginning of the alcoholic fermentation in order to optimize the fermentation environment and release antioxidant and characterizing components. LIFTY Sense is an innovative additive to be used at the beginning of the alcoholic fermentation in order to optimize the fermentation environment and release antioxidant and characterizing components.') ?>
            </div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/nutrients/142-lifty-sense.html">
            <span class="text-truncate">Lifty Sense</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item N4 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="nutrients">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/nutrients/147-wyntube-fructal.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/wyntube-fructal.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.n4', 'Wine Yeast Nutrient in miniTubes™: the miniTubes technology applied to an yeast nutrient specifically designed for fermentation practices that aim to maximize fruit expression by the yeast.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/nutrients/147-wyntube-fructal.html">
            <span class="text-truncate">wynTube Fructal</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item N5 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="nutrients">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/nutrients/148-wyntube-full.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/wyntube-full.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.n5', 'Wine Yeast Nutrient in miniTubes™: the miniTubes technology applied to an yeast nutrient specifically designed for fermentation practices that aim to maximize fruit expression by the yeast.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/nutrients/148-wyntube-full.html">
            <span class="text-truncate">wynTube Full</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item N6 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="nutrients">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/nutrients/162-wyntube-prepara.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/wyntube-prepara.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.n6', 'Wine Yeast Nutrient in miniTubes™: we have applied the miniTubes technology to a specific nutrient for yeast rehydration.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/nutrients/162-wyntube-prepara.html">
            <span class="text-truncate">wynTube Prepara</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item N7 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="nutrients">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <!--
          <span class="badge text-bg-danger position-absolute top-0 start-0 z-2 mt-2 mt-sm-3 ms-2 ms-sm-3">-17%</span>
          -->
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/nutrients/163-wyntube-prolife.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/wyntube-prolife.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.n7', 'Wine Yeast Nutrient in miniTubes™: we have applied the miniTubes technology to a specific nutrient to detoxify the juice/wine and to regulate alcoholic fermentation.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/nutrients/163-wyntube-prolife.html">
            <span class="text-truncate">wynTube ProLife</span>
          </a>
        </div>
        <div class="h6 mb-2"><del class="fs-sm fw-normal text-body-tertiary"></del></div>
      </div>
    </div>

    <!-- Item N8 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="nutrients">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/nutrients/164-wyntube-revelathiol.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/wyntube-revelathiol.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.n8', 'Wine Yeast Nutrient in miniTubes™: the miniTubes technology applied to an yeast nutrient in order to increase thiol aromas.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/nutrients/164-wyntube-revelathiol.html">
            <span class="text-truncate">wynTube RevelaThiol</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- ===================== ENZYMES (8 ks, ZKOPÍROVANÉ) ===================== -->
    <!-- Item E1 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="enzymes">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <!--
          <span class="badge text-bg-danger position-absolute top-0 start-0 z-2 mt-2 mt-sm-3 ms-2 ms-sm-3">Sale</span>
          -->
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/enzymes/168-aromazina-dc.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/aromazina.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.e1', 'Aroma and structure expression for varietal white wines. During the grape skin maceration, it heightens the extraction of the varietal aromas and gives the final wines a complex sensory profile.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/enzymes/168-aromazina-dc.html">
            <span class="text-truncate">Aromazina DC</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Item E2 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="enzymes">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/enzymes/172-ultrasi-g.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/ultrasi-g.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.e2', 'Are specific pectolytic enzymes for fining and clarifying white grape musts, in different areas and conditions, in a short time. ULTRasi G and ULTRasi L are specific pectolytic enzymes for fining and clarifying white grape musts, in different areas and conditions, in a short time (few hours).') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/enzymes/172-ultrasi-g.html">
            <span class="text-truncate">ULTRasi G</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item E3 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="enzymes">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/enzymes/170-ultrasi-darkberry.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/ultrasi-darberry.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.e3', 'Powder group of enzymes with pectolitic action. It also shows a strong extraction power of colour (anthocyans) in red grapes. ULTRasi Darkberry offers both pectolytic and secondary actions that work synergistically to give a rapid extraction of anthocyanins and non-astringent tannins from the grape skins during maceration.') ?>
            </div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/enzymes/170-ultrasi-darkberry.html">
            <span class="text-truncate">ULTRasi Darkberry</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item E4 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="enzymes">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/enzymes/169-ultrasi-4skin.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/ultrasi-4skin.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.e4', 'Aroma and structure expression for varietal white wines. During the grape skin maceration, it heightens the extraction of the varietal aromas and gives the final wines a complex sensory profile.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/enzymes/169-ultrasi-4skin.html">
            <span class="text-truncate">ULTRasi 4Skin</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item E5 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="enzymes">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/enzymes/171-ultrasi-flot.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/ultrasi-flot.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.e5', 'Liquid enzyme that expresses its full potential when preparing the must for flotation process, since it is characterized by an excellent combination of fundamental pectolytic activities.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/enzymes/171-ultrasi-flot.html">
            <span class="text-truncate">ULTRasi Flot</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item E6 
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="enzymes">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/yeast/138-lalvin-r7.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/lalvin r7.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text">Ready and quick fermentation even when arrested. No special activation treatments are required. Very high alcohol yield. This strain is characterized by the rapid start-up of the fermentation process, even in the higher presence of alcohol and sugar. The ability to produce alcohol is extremely high (up to 19 degrees).</div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/yeast/138-lalvin-r7.html">
            <span class="text-truncate">Wine Yeast Lalvin R7®</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>
-->
    <!-- Item E7 
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="enzymes">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
-->          
          <!--
          <span class="badge text-bg-danger position-absolute top-0 start-0 z-2 mt-2 mt-sm-3 ms-2 ms-sm-3">-17%</span>
          -->
          <!--
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/yeast/139-lalvin-s6u.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/Lalvin S6U.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text">For the characterization of white and red wines. Exaltation of spicy notes, high production of glycerol, marked cryophilia. Indicated in late harvests. This strain is able to develop 1.2 g/l more glycerine during fermentation than other yeast cultures. They are therefore suitable for optimum extract increase in wines (musts) with low natural extract. It ensures a good alcohol level (14°C).</div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/yeast/139-lalvin-s6u.html">
            <span class="text-truncate">Wine Yeast Lalvin S6U™</span>
          </a>
        </div>
        <div class="h6 mb-2"><del class="fs-sm fw-normal text-body-tertiary"></del></div>
      </div>
    </div>
-->
    <!-- Item E8 
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="enzymes">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/yeast/136-wine-yeast-lalvin-cy3079-yseo.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/lalvin cy3079.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text">For the processing of great white wines, especially from Chardonnay vines. Suitable for fermentation in barrique. This strain greatly enhances the primary aromas in Chardonnay varieties, lending them considerable length and great complexity. During autolysis, it releases complex aromas that complement the floral and fruity characteristics typical of fermentation.</div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/yeast/136-wine-yeast-lalvin-cy3079-yseo.html">
            <span class="text-truncate">Wine Yeast Lalvin CY3079 YSEO®</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>
-->
    <!-- ===================== TANNINS (8 ks, ZKOPÍROVANÉ) ===================== -->
    <!-- (T1–T8: stejné položky jako YEAST, pouze data-cat="tannins") -->

    <!-- Item T1 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="tannins">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <!--
          <span class="badge text-bg-danger position-absolute top-0 start-0 z-2 mt-2 mt-sm-3 ms-2 ms-sm-3">Sale</span>
          -->
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/tannins/192-tanniferm-flash.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/Tanniferm-Flash.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.t1', 'Mix of tannins. It can be used since the beginning of vinification in order to preserve anthocyane molecules and enhance both mouth feel of final wine. Tanniferm is an ellagic and proanthocyanidic tannins mix. Thank to its composition, this product can be used since the beginning of vinification, just after the crusher, in order to preserve anthocyane molecules to oxidative reactions.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/tannins/192-tanniferm-flash.html">
            <span class="text-truncate">Tanniferm Flash</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Item T2 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="tannins">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/tannins/193-tannirouge-flash.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/tannirouge-flash.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.t2', 'Tannin from quebracho, specific for red wines, with improving of organoleptic complexity and colour stability. Tannirouge Flash is a reddish tannin, obtained from the best “quebracho” wood. Regarding the composition, quebracho tannin has mainly a “procyanidin” structure.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/tannins/193-tannirouge-flash.html">
            <span class="text-truncate">Tannirouge Flash</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item T3 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="tannins">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/tannins/191-tannex-flash.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/tannex-flash.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.t3', 'Tannin for the best stability of the colour extracted by proper enzymes during the red grape fermentation. Tannex Flash is used during red wine fermentation. The addition of Tannex Flash during tank filling will ensure the stabilisation of free antocyanins likely to be subjected to oxidative reactions and subsequent precipitation.') ?>
            </div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/tannins/191-tannex-flash.html">
            <span class="text-truncate">Tannex Flash</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item T4 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="tannins">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/tannins/187-infinity-redox.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/infinity-redox.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.t4', 'To protect white and rosé wines from oxidation. Already from the end of the alcoholic fermentation, Infinity Redox protects white and rosé wines from oxidative phenomena, both during the storage in stainless steel tanks as well as during racking.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/tannins/187-infinity-redox.html">
            <span class="text-truncate">Infinity Redox</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item T5 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="tannins">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/tannins/185-infinity-decuvage.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/infinity-decuvage.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.t5', 'To protect and stabilize red wine colour. When used at devatting Infinity Décuvage allows for an initial polymerization of anthocyanins and hence promote colour stabilization.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/tannins/185-infinity-decuvage.html">
            <span class="text-truncate">Infinity Décuvage</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item T6 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="tannins">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/tannins/190-tannino-q.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/Tannino-Q.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.t6', 'Very pure oak tannin for the best action on the bouquet of red and white wines, especially the aged ones. Tannino Q is a tannin obtained from the best oak grew in Allier and Limousin forests, applied to improve the organoleptic characteristic of important red and white wines.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/tannins/190-tannino-q.html">
            <span class="text-truncate">Tannino Q</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item T7 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="tannins">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <!--
          <span class="badge text-bg-danger position-absolute top-0 start-0 z-2 mt-2 mt-sm-3 ms-2 ms-sm-3">-17%</span>
          -->
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/tannins/186-infinity-fruity-white.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/infinity-fruity-white.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.t7', 'This blend gives exciting results in terms of white wine revitalization. Used for finishing touches and before bottling it removes off-flavour as reductive notes and it improves expression of the fruity and floral aromas.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/tannins/186-infinity-fruity-white.html">
            <span class="text-truncate">Infinity Fruity White</span>
          </a>
        </div>
        <div class="h6 mb-2"><del class="fs-sm fw-normal text-body-tertiary"></del></div>
      </div>
    </div>

    <!-- Item T8 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="tannins">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/tannins/188-infinity-roble.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/infinity-roble.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.t8', 'Complexity, elegance and structure for white, rosé and red wines. Infinity Roble is an oak wood extracted tannin based solution. The raw material from which it is extracted undergoes a long aging process of at least 36 months in the open air in order to eliminate any herbaceous note or undesirable astringency.') ?>
            </div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/tannins/188-infinity-roble.html">
            <span class="text-truncate">Infinity Roble</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- ===================== BENTONITES (8 ks, ZKOPÍROVANÉ) ===================== -->
    <!-- (B1–B8: stejné položky jako YEAST, pouze data-cat="bentonites") -->

    <!-- Item B1 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="bentonites">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <!--
          <span class="badge text-bg-danger position-absolute top-0 start-0 z-2 mt-2 mt-sm-3 ms-2 ms-sm-3">Sale</span>
          -->
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/bentonites/112-ciridlo-a-stabilizator-albakollb.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/Albakoll-R.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.b1', 'Fining powder based on bentonites and protein groups, stabilised with small quantities of carbon. Suggested for white wines rich of colloids, iron or easily oxidable.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/bentonites/112-ciridlo-a-stabilizator-albakollb.html">
            <span class="text-truncate">Albakoll™B</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Item B2 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="bentonites">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/bentonites/113-albakoll-r.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/Albakoll-R.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.b2', 'Fining powder based on bentonites and protein groups, suggested for fining and stabilization of red wines rich of colloids or tannins. It is also suggested for musts.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/bentonites/113-albakoll-r.html">
            <span class="text-truncate">Albakoll™ R</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item B3 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="bentonites">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/bentonites/116-phytokoll-app.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/Phytokoll-App.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.b3', 'The perfect mix of potato and pea protein. During the fining of musts, static sedimentation or flotation, and wines it leads to the clarification and the removal of oxidized colour fractions.') ?>
            </div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/bentonites/116-phytokoll-app.html">
            <span class="text-truncate">Phytokoll™ App</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item B4 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="bentonites">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/bentonites/100-gelbentonite-dc.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/gelbentonite-dc.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.b4', 'Activated purified bentonite filaments with very high absorbing power to give brilliance to wine at low rates. Gelbentonite DC has been improved and now it’s in filaments form. The raw material is selected specifically for this purpose and goes through several complex transformations in order to eliminate any contaminants from the pure montmorillonite.') ?>
            </div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/bentonites/100-gelbentonite-dc.html">
            <span class="text-truncate">Gelbentonite DC</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item B5 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="bentonites">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/bentonites/115-224-topgran.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/topgran.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.b5', 'The bentonite that responds to the need for protein stability and clarification without waste or sacrifices in terms of sensory quality objectives. Topgran+ is a bentonite that responds to the need for protein stability and clarification without waste or sacrifices in terms of sensory quality objectives.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/bentonites/115-224-topgran.html">
            <span class="text-truncate">Topgran™+</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item B6 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="bentonites">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/bentonites/114-bento-zero.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/bento-zero.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.b6', 'No sediment. No waiting. No residues. Bento.Zero is a bentonite that results research efforts. It takes into account modern fining concepts: a rapid process that respects and valorises the sensory characteristics of the product to be treated. Bento.Zero is particular thanks to its instantaneous preparation, the minimum quantity of water needed for the rehydration, the very low sediment and its protein stabilization efficacy.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/bentonites/114-bento-zero.html">
            <span class="text-truncate">Bento.Zero</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>

    <!-- Item B7 -->
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="bentonites">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <!--
          <span class="badge text-bg-danger position-absolute top-0 start-0 z-2 mt-2 mt-sm-3 ms-2 ms-sm-3">-17%</span>
          -->
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/bentonites/95-superbenton-dc.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/superbenton-dc.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text"><?= t('eno.b7', 'Activated powder bentonite for musts and wines with high deproteinizing action. It is the traditional bentonite of DAL CIN SPA, activated by an original process since 1949. This activating process has been sensibly improved according to the new technologies. Superbenton DC powder is characterised by an extraordinary fining and deproteinizing power.') ?></div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/bentonites/95-superbenton-dc.html">
            <span class="text-truncate">Superbenton DC</span>
          </a>
        </div>
        <div class="h6 mb-2"><del class="fs-sm fw-normal text-body-tertiary"></del></div>
      </div>
    </div>

    <!-- Item B8 
    <div class="col mb-2 mb-sm-3 mb-md-0" data-cat="bentonites">
      <div class="animate-underline hover-effect-opacity">
        <div class="position-relative mb-3 product-card">
          <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
            <i class="ci-heart animate-target"></i>
          </button>
          <a class="d-flex bg-body-tertiary rounded p-3" href="/shop/<?= $lang ?>/yeast/136-wine-yeast-lalvin-cy3079-yseo.html">
            <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
              <img src="/x-assets/img/products/lalvin cy3079.jpg" alt="Image">
            </div>
          </a>
          <div class="product-overlay rounded-2">
            <div class="overlay-text">For the processing of great white wines, especially from Chardonnay vines. Suitable for fermentation in barrique. This strain greatly enhances the primary aromas in Chardonnay varieties, lending them considerable length and great complexity. During autolysis, it releases complex aromas that complement the floral and fruity characteristics typical of fermentation.</div>
          </div>
        </div>
        <div class="nav mb-2">
          <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/shop/<?= $lang ?>/yeast/136-wine-yeast-lalvin-cy3079-yseo.html">
            <span class="text-truncate">Wine Yeast Lalvin CY3079 YSEO®</span>
          </a>
        </div>
        <div class="h6 mb-2"></div>
      </div>
    </div>
-->
  </div>

  <!-- Filtrační logika -->
  <script>
    (function () {
      const filters = document.querySelectorAll('#enoFilters [data-filter]');
      const cards = document.querySelectorAll('#enoGrid [data-cat]');

      function norm(str) {
        return (str || '')
          .split(',')
          .map(s => s.trim().toLowerCase())
          .filter(Boolean);
      }

      function applyFilter(filter) {
        const f = (filter || 'yeast').toLowerCase();
        cards.forEach(card => {
          const cats = norm(card.dataset.cat);
          const hide = f !== 'all' && !cats.includes(f);
          card.classList.toggle('d-none', hide);
        });
      }

      filters.forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          filters.forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          applyFilter(btn.dataset.filter);
        });
      });

      // inicializace dle aktivní pilulky (Yeast)
      const active = document.querySelector('#enoFilters .nav-link.active[data-filter]');
      applyFilter(active ? active.dataset.filter : 'yeast');
    })();
  </script>
</section>







      <!-- Gallery 
      <section class="container pb-5 mb-sm-2 mb-md-3 mb-lg-4 mb-xl-5">
        <h2 class="h3 pb-3">Interior design and inspiration</h2>
-->
        <!-- Nav pills 
        <nav class="overflow-x-auto mb-3" data-simplebar data-simplebar-auto-hide="false">
          <ul class="nav nav-pills flex-nowrap text-nowrap pb-3">
            <li class="nav-item">
              <a class="nav-link active" aria-current="page" href="#!">Living room</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#!">Bedroom</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#!">Kitchen</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#!">Decoration</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#!">Office</a>
            </li>
          </ul>
        </nav>
-->
        <!-- Grid 
        <div class="row g-4 g-sm-3 g-lg-4 mb-xxl-3">
          <div class="col-sm-5 d-flex flex-column gap-4 gap-sm-3 gap-lg-4">
-->
            <!-- Item with hotspots 
            <div class="ratio" id="hotspots" style="--cz-aspect-ratio: calc(500 / 526 * 100%)">
-->              
              <!-- Hide when the direction is set to RTL
              <a class="btn btn-icon btn-sm btn-light rounded-circle shadow position-absolute z-2 d-none-rtl" href="#!" style="top: 63.4%; left: 75.8%" data-bs-toggle="popover" data-bs-html="true" data-bs-trigger="focus" data-bs-placement="top" data-bs-custom-class="popover-sm" data-bs-content='
                <div class="d-flex align-items-start position-relative">
                  <img src="/x-assets/img/home/furniture/gallery/hotspot01.png" width="64" alt="Image">
                  <div class="nav flex-column pt-2 ps-2 ms-1">
                    <a class="nav-link hover-effect-underline stretched-link p-0 mb-2" href="shop-product-furniture.html">Indigo coushy low sofa</a>
                    <div class="h6 mb-0">$856.00</div>
                  </div>
                </div>
              ' tabindex="1" aria-label="Hotspot">
                <i class="ci-plus fs-sm"></i>
              </a>
 -->              
              <!-- Show when the direction is set to RTL 
              <a class="btn btn-icon btn-sm btn-light rounded-circle shadow position-absolute z-2 d-none d-flex-rtl" href="#!" style="top: 63.4%; right: 18.5%" data-bs-toggle="popover" data-bs-html="true" data-bs-trigger="focus" data-bs-placement="top" data-bs-custom-class="popover-sm" data-bs-content='
                <div class="d-flex align-items-start position-relative">
                  <img src="/x-assets/img/home/furniture/gallery/hotspot01.png" width="64" alt="Image">
                  <div class="nav flex-column pt-2 ps-2 ms-1">
                    <a class="nav-link hover-effect-underline stretched-link p-0 mb-2" href="shop-product-furniture.html">Indigo coushy low sofa</a>
                    <div class="h6 mb-0">$856.00</div>
                  </div>
                </div>
              ' tabindex="1" aria-label="Hotspot">
                <i class="ci-plus fs-sm"></i>
              </a>
-->              
              <!-- Hide when the direction is set to RTL 
              <a class="btn btn-icon btn-sm btn-light rounded-circle shadow position-absolute z-2 d-none-rtl" href="#!" style="top: 60.2%; left: 15.7%" data-bs-toggle="popover" data-bs-html="true" data-bs-trigger="focus" data-bs-placement="bottom" data-bs-custom-class="popover-sm" data-bs-content='
                <div class="d-flex align-items-start position-relative">
                  <img src="/x-assets/img/home/furniture/gallery/hotspot02.png" width="64" alt="Image">
                  <div class="nav flex-column pt-2 ps-2 ms-1">
                    <a class="nav-link hover-effect-underline stretched-link p-0 mb-2" href="shop-product-furniture.html">Ergonomic beige armchair</a>
                    <div class="h6 mb-0">$235.00</div>
                  </div>
                </div>
              ' tabindex="1" aria-label="Hotspot">
                <i class="ci-plus fs-sm"></i>
              </a>
-->              
              <!-- Show when the direction is set to RTL 
              <a class="btn btn-icon btn-sm btn-light rounded-circle shadow position-absolute z-2 d-none d-flex-rtl" href="#!" style="top: 60%; right: 78%" data-bs-toggle="popover" data-bs-html="true" data-bs-trigger="focus" data-bs-placement="bottom" data-bs-custom-class="popover-sm" data-bs-content='
                <div class="d-flex align-items-start position-relative">
                  <img src="/x-assets/img/home/furniture/gallery/hotspot02.png" width="64" alt="Image">
                  <div class="nav flex-column pt-2 ps-2 ms-1">
                    <a class="nav-link hover-effect-underline stretched-link p-0 mb-2" href="shop-product-furniture.html">Ergonomic beige armchair</a>
                    <div class="h6 mb-0">$235.00</div>
                  </div>
                </div>
              ' tabindex="1" aria-label="Hotspot">
                <i class="ci-plus fs-sm"></i>
              </a>
              <a class="btn btn-icon btn-sm btn-light rounded-circle shadow position-absolute z-2 start-50 translate-middle-x" href="#!" style="top: 25.8%" data-bs-toggle="popover" data-bs-html="true" data-bs-trigger="focus" data-bs-placement="top" data-bs-custom-class="popover-sm" data-bs-content='
                <div class="d-flex align-items-start position-relative">
                  <img src="/x-assets/img/home/furniture/gallery/hotspot03.png" width="64" alt="Image">
                  <div class="nav flex-column pt-2 ps-2 ms-1">
                    <a class="nav-link hover-effect-underline stretched-link p-0 mb-2" href="shop-product-furniture.html">Waves modern painting</a>
                    <div class="h6 mb-0">$74.99</div>
                  </div>
                </div>
              ' tabindex="1" aria-label="Hotspot">
                <i class="ci-plus fs-sm"></i>
              </a>
              <img src="/x-assets/img/home/furniture/gallery/01.jpg" class="rounded-5" alt="Image">
            </div>
-->
            <!-- Item 
            <div class="ratio" style="--cz-aspect-ratio: calc(529 / 526 * 100%)">
              <img src="/x-assets/img/home/furniture/gallery/02.jpg" class="rounded-5" alt="Image">
            </div>
          </div>
          <div class="col-sm-7 d-flex flex-column gap-4 gap-sm-3 gap-lg-4">
-->
            <!-- Item 
            <div class="ratio" style="--cz-aspect-ratio: calc(664 / 746 * 100%)">
              <img src="/x-assets/img/home/furniture/gallery/03.jpg" class="rounded-5" alt="Image">
            </div>
-->
            <!-- Item 
            <div class="ratio" style="--cz-aspect-ratio: calc(365 / 746 * 100%)">
              <img src="/x-assets/img/home/furniture/gallery/04.jpg" class="rounded-5" alt="Image">
            </div>
          </div>
        </div>
      </section>
-->

      <!-- Features 
      <section class="container pb-5 mb-sm-2 mb-md-3 mb-lg-4 mb-xl-5">
        <div class="row row-cols-1 row-cols-md-3 gy-3 gy-sm-4 gx-2 gx-lg-4 mb-xxl-3">
          <div class="col text-center">
            <svg class="d-block text-dark-emphasis mx-auto mb-3 mb-lg-4" xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64" fill="currentColor"><path d="M62.189 9.902c0-.604-.604-1.208-1.208-1.208h-6.158-3.14l-1.69.121-1.57.242c-2.174.483-4.226 1.087-6.158 2.174s-3.623 2.294-5.072 3.864h-.121c-3.14 3.019-5.313 7.004-6.038 11.351l-.241 1.57-.121 1.691v3.14 5.796c-.604.845-1.087 1.691-1.57 2.536.121-1.328.121-2.536.241-3.864 0-.966.121-1.811.121-2.777v-1.449l-.121-1.449c-.241-1.811-.845-3.743-1.691-5.434a20.6 20.6 0 0 0-3.26-4.71c-2.657-2.777-6.279-4.709-10.143-5.434L12.8 15.82l-1.449-.121H8.574 3.019c-.604 0-1.208.604-1.208 1.208v5.555 2.777l.121 1.449.242 1.449C2.898 32 4.83 35.502 7.729 38.159c1.449 1.328 3.019 2.415 4.709 3.26s3.623 1.328 5.434 1.691l1.449.121h1.449c.966 0 1.811-.121 2.777-.121 1.57-.121 3.14-.121 4.709-.242-.362.604-.604 1.328-.966 1.932-1.449 3.381-2.294 7.004-2.294 10.506.966-3.502 2.294-6.642 3.985-9.66.966-1.811 2.174-3.623 3.381-5.313h5.675 3.14l1.691-.121 1.57-.242c2.174-.483 4.227-1.087 6.159-2.174s3.623-2.294 5.072-3.864h.121c3.14-3.019 5.313-7.004 6.038-11.351l.242-1.57.121-1.69v-3.14-6.279zM49.63 35.743c-1.691.966-3.623 1.449-5.555 1.811l-1.449.242-1.449.121h-3.019-3.864c.242-.242.483-.604.725-.845 2.174-2.657 4.589-5.192 7.004-7.728l7.366-7.728c-3.019 1.932-5.917 3.985-8.694 6.279-2.657 2.294-5.192 4.709-7.487 7.487v-2.536-3.019l.121-1.449.242-1.449c.362-1.932.845-3.864 1.811-5.555.845-1.691 2.053-3.381 3.381-4.83 1.449-1.328 3.019-2.415 4.709-3.381s3.623-1.449 5.555-1.811l1.449-.241 1.449-.121h3.019 4.951v4.951 3.019l-.121 1.57-.242 1.449c-.362 1.932-.845 3.864-1.811 5.555-.845 1.691-2.053 3.381-3.381 4.83-1.449 1.449-3.019 2.536-4.709 3.381zm-26.083 6.762c-.966 0-1.811-.121-2.777-.121l-1.328-.121-1.328-.242c-3.502-.724-6.641-2.536-9.057-5.072-1.208-1.328-2.174-2.657-3.019-4.226-.725-1.57-1.208-3.26-1.57-4.951l-.242-1.328-.121-1.328V22.34v-4.347h4.347 2.777 1.328l1.328.121c1.691.242 3.381.725 4.951 1.57 1.449 1.087 2.898 2.053 4.106 3.26 2.536 2.415 4.347 5.555 5.072 9.057l.241 1.328.121 1.328c.121.845.121 1.811.121 2.777.121 1.449.121 2.777.241 4.226-.241.483-.483.845-.724 1.328-1.328-.362-2.898-.362-4.468-.483zm-5.434-12.437c-1.449-.966-2.898-1.932-4.589-2.657.966 1.449 2.174 2.777 3.381 3.985 2.415 2.536 4.83 4.709 7.487 7.124 1.328 1.087 2.536 2.294 4.106 3.381-.725-1.691-1.57-3.26-2.657-4.589-2.174-2.898-4.709-5.193-7.728-7.245z"/></svg>
            <h3 class="h5">Eco-friendly</h3>
            <p class="fs-sm px-5 mb-md-0">Decorate your space with eco-friendly furniture with low VOCs, environmentally friendly materials and safe coatings.</p>
          </div>
          <div class="col text-center">
            <svg class="d-block text-dark-emphasis mx-auto mb-3 mb-lg-4" xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64" fill="currentColor"><path d="M55.526 24.465l-.145-10.159-.094-5.08-.012-.635c-.016-.23-.016-.481-.061-.717-.06-.481-.22-.945-.413-1.384-.407-.875-1.061-1.625-1.868-2.136a4.99 4.99 0 0 0-2.699-.769l-2.548.061-15.238.437 15.238.437 2.532.069a3.93 3.93 0 0 1 2.088.71c.601.431 1.085 1.017 1.365 1.692.131.339.242.688.27 1.051.029.181.017.356.026.548l-.012.635-.094 5.08-.119 8.281c-3.476-.415-6.952-.651-10.428-.808-3.769-.185-7.537-.235-11.306-.255-3.769.023-7.537.073-11.306.258-3.471.158-6.941.392-10.412.803l-.131-9.156-.085-5.05c.009-1.448.949-2.849 2.313-3.435.691-.318 1.391-.355 2.28-.357l2.54-.066 15.239-.439-17.778-.505c-.425-.009-.83-.032-1.325-.006-.472.048-.941.145-1.388.317-1.798.674-3.123 2.475-3.216 4.432l-.105 5.109-.145 10.159-.111 10.159-.046 5.714c.011.242.006.518.048.774.054.523.214 1.031.415 1.516.421.967 1.122 1.802 1.996 2.394a5.52 5.52 0 0 0 2.985.937l1.885.008a219.85 219.85 0 0 0-2.615 7.372l-1.399 4.349-.166.552a2.42 2.42 0 0 0-.062 1.062c.109.703.567 1.362 1.196 1.705a2.42 2.42 0 0 0 2.973-.484c.144-.164.17-.207.235-.287l.177-.224c3.518-4.56 6.926-9.206 10.121-14.015l5.451.014 6.309-.017c3.205 4.808 6.615 9.457 10.14 14.017l.177.224c.065.081.092.123.235.287.753.837 2.009 1.035 2.971.484.629-.343 1.086-1.001 1.195-1.704a2.42 2.42 0 0 0-.062-1.061l-.166-.552-1.403-4.349a228.34 228.34 0 0 0-2.625-7.375l1.007-.003c.425-.003.814.01 1.383-.037.524-.067 1.042-.192 1.53-.396 1.966-.798 3.353-2.796 3.404-4.903l-.03-5.126-.112-10.159zM14.167 57.718l-.293.386c-.011.016-.023.027-.04.035-.035.018-.081.021-.114.004-.043-.018-.066-.046-.08-.095a.18.18 0 0 1-.002-.069l.153-.545 1.157-4.419c.65-2.627 1.271-5.264 1.822-7.92l7.761.02c-3.608 4.082-7.037 8.303-10.363 12.603zm34.91-4.704l1.155 4.42.153.544a.19.19 0 0 1-.002.07c-.014.049-.037.077-.081.096-.034.018-.08.015-.115-.004-.018-.008-.029-.019-.041-.035l-.294-.386c-3.321-4.302-6.749-8.521-10.35-12.607l7.309-.02.452-.001c.549 2.657 1.17 5.293 1.814 7.922zm4.528-18.39l-.05 5.033c-.051 1.297-.928 2.501-2.124 2.963-.626.251-1.14.252-2.08.235l-17.778-.047c-25.374.066 7.11-.017-17.761.043a3.42 3.42 0 0 1-1.802-.541 3.43 3.43 0 0 1-1.238-1.434c-.123-.294-.234-.599-.268-.92-.032-.162-.025-.312-.04-.492l-.046-5.714-.112-10.159-.011-.8c3.47.411 6.94.645 10.409.803 3.769.186 7.537.235 11.306.258 3.769-.02 7.537-.07 11.306-.255 3.475-.157 6.95-.393 10.425-.808l-.024 1.677-.112 10.159zm-14.693-23.94c-3.673-1.557-10.14-1.544-13.805 0 3.66 1.542 10.125 1.559 13.805 0zM25.107 28.829c3.647 1.537 10.114 1.564 13.805 0-3.697-1.567-10.167-1.533-13.805 0z"/></svg>
            <h3 class="h5">Unbeatable quality</h3>
            <p class="fs-sm px-5 mb-md-0">We choose raw materials from the best manufacturers, so our furniture and decor are of the highest quality at the best prices.</p>
          </div>
          <div class="col text-center">
            <svg class="d-block text-dark-emphasis mx-auto mb-3 mb-lg-4" xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64" fill="currentColor"><path d="M5.36 29.423c.111 0 2.111 1 6.333 2.667l-.222 10.222c-.111 3.667-.111 7.334-.111 11a1.07 1.07 0 0 0 .778 1c.333.111 10 2.778 9.889 2.667.111 0 10 2.667 9.889 2.667h.111.111.111.111.111l9.889-2.667c.111 0 10-2.778 9.889-2.667a1.07 1.07 0 0 0 .778-1c-.111-3.667-.111-7.334-.111-11l-.222-10.222 6.222-2.667c.111 0 .111-.111.222-.111.222-.222.333-.667 0-.889l-7.111-7.556 5.556 7.667c-3.111 1-6.111 2-9.111 3.111l-9.111 3.333-5.333-6.334c6.222-2.556 12-5.111 18.112-7.889-6-2.778-11.889-5.333-18-7.889l5.333-6.333 9.111 3.333c3 1.111 6.111 2.111 9.111 3.111l-5.556 7.667c2.333-2.333 4.778-4.889 7.111-7.556 0 0 .111-.111.111-.222.111-.333 0-.667-.333-.889-3.222-1.444-6.445-2.778-9.778-4l-9.778-3.889c-.444-.222-.889 0-1.222.333l-5.778 7.111c-1.889-2.445-3.889-4.778-5.778-7.111-.333-.333-.778-.556-1.222-.333-3.222 1.222-6.556 2.556-9.778 3.889-3.778 1.444-7 2.778-10.222 4.222-.111 0-.222.111-.222.111-.222.222-.333.667 0 .889 2.111 2.333 4.333 4.667 6.334 6.778-.444.444-.444 1.222 0 1.556-2.111 2.111-4.222 4.444-6.334 6.778-.444.444-.222 1 .111 1.111zm6.556-7.556l9.889 3.556 3.778 1.333 4.778 2-5.333 6.334-9.111-3.333c-3-1.111-6.111-2.111-9.111-3.111 1.667-2.222 3.222-4.444 4.889-6.778 0-.111.111 0 .222 0zm1.445 30.667c0-3.111-.111-9.556-.445-19.889.778.333 1.445.667 2.222.889l9.778 3.889c.444.222.889 0 1.222-.333 1.778-2.111 3.556-4.333 5.222-6.444-.111 1.778-.111 3.556-.111 5.333l-.222 7.556-.222 13.778-8.556-2.333-8.889-2.444zm37.334 0l-9.111 2.444-8.556 2.333c0-5.778-.111-7.222-.222-13.778l-.222-7.556c-.111-1.778-.111-3.556-.111-5.333 1.778 2.111 3.445 4.333 5.222 6.444.333.333.778.556 1.222.333 3.222-1.222 6.556-2.556 9.778-3.889.778-.333 1.444-.556 2.222-.889l-.222 19.889zm.889-31.667c-5.334 1.556-9.111 2.778-13.556 4.333-2 .667-4 1.222-6 1.889l-9.556-3.667-7-2.556 7-2.556 9.556-3.667c1.889.667 3.778 1.222 5.667 1.889l14 4.445c0-.111-.111-.111-.111-.111zm-44.89-7.667l9.222-3.222 9.111-3.333 5.333 6.333c-1.556.667-3.111 1.222-4.667 1.889l-3.889 1.333-9.889 3.556c-.111 0-.222.111-.222.111l-5-6.667z"/></svg>
            <h3 class="h5">Delivery to your door</h3>
            <p class="fs-sm px-5 mb-md-0"> We will deliver to your door anywhere in the world. If you're not 100% satisfied, let us know within 30 days and we'll solve the problem.</p>
          </div>
        </div>
      </section>
-->

      <!-- Featured product with video 
      <section class="container">
        <div class="row row-cols-1 row-cols-md-2 g-0 overflow-hidden rounded-5">
-->
          <!-- Video 
          <div class="col position-relative">
            <div class="ratio ratio-1x1 d-none d-md-block"></div>
            <div class="ratio ratio-4x3 d-md-none"></div>
            <div class="position-absolute top-0 start-0 w-100 h-100 bg-body-secondary"></div>
            <img src="/x-assets/img/home/furniture/featured-product.png" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover z-1" alt="Image">
            <div class="position-absolute start-0 bottom-0 d-flex align-items-end w-100 h-100 z-2 p-4">
              <a class="btn btn-lg btn-light rounded-pill m-md-2" href="https://www.youtube.com/watch?v=Z1xX1Kt9NkU" data-glightbox data-gallery="video">
                <i class="ci-play fs-lg ms-n1 me-2"></i>
                Play
              </a>
            </div>
          </div>
-->
          <!-- Featured product 
          <div class="col d-flex align-items-center justify-content-center bg-dark py-5 px-4 px-md-5" data-bs-theme="dark">
            <div class="text-center py-md-2 py-lg-3 py-xl-4" style="max-width: 400px">
              <div class="fs-xs fw-medium text-body text-uppercase mb-3">Best deal</div>
              <h2 class="h4 pb-lg-2 pb-xl-0 mb-4 mb-xl-5">Scandinavian green chair with wooden legs 60x100 cm</h2>
              <div class="d-inline-flex pb-lg-2 pb-xl-0 mb-4 mb-xl-5">
                <img src="/x-assets/img/home/furniture/featured-product-thumbnail.jpg" class="rounded" width="162" alt="Product">
              </div>
              <div class="h3 pb-2 pb-md-3">$357.00</div>
              <a class="btn btn-lg btn-outline-light rounded-pill" href="shop-product-furniture.html">Shop now</a>
            </div>
          </div>
        </div>
      </section>
-->


      <!-- Reviews -->
      <section class="pt-2 pt-sm-3 pt-md-4 pt-lg-5 pb-5 my-xxl-3">
        <div class="position-relative py-2 py-sm-3 py-md-4 py-lg-5">
          <div class="container position-relative z-2 py-5 my-xxl-3">

            <!-- Header -->
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 gap-sm-4 pb-xl-0-3 mb-4 mb-xl-5">
              <h3 class="h2 display-4 text-center text-sm-start mb-0"><?= t('rev.h3', 'They’re happy with us') ?></h3>
              <div class="nav justify-content-center justify-content-sm-start">
                <a class="nav-link fs-base position-relative text-center text-sm-start px-0" href="https://www.google.com/search?sca_esv=82ceebf1275fd2c3&rlz=1C1YTUH_csCZ1009CZ1009&hl=cs-CZ&biw=1226&bih=812&sxsrf=AE3TifNOceuX7vA0Co62Fe-TxU_oTiUi_w:1756577535804&si=AMgyJEvkVjFQtirYNBhM3ZJIRTaSJ6PxY6y1_6WZHGInbzDnMZOHdpYHHnyC9hhoUwIeM5sQnfu9faoeEqDw3xhBOXlXLpe2KRap96pKW8S0XfpqdD6f24eQ5naP1y9It2EpyQi-j0pcQGjdQDBDdVXM9PDxwCF83w%3D%3D&q=ZAN-AROMI,+spol.+s+r.o.+Recenze&sa=X&ved=2ahUKEwjM4qqgkbOPAxUzR_EDHaphMrAQ0bkNegQIKBAE">
                  <span class="hover-effect-underline stretched-link">50+ <?= t('rev.b', 'real reviews on Google') ?></span>
                  <i class="ci-chevron-right fs-lg ms-1 me-n1"></i>
                </a>
              </div>
            </div>

            <!-- Reviews grid -->
            <div class="row g-4">
              <div class="col-lg-4 d-flex flex-column flex-md-row flex-lg-column gap-4">

                <!-- Review -->
                <div class="card w-100 bg-transparent border-0 rounded-5 overflow-hidden p-xl-2">
                  <div class="card-body position-relative z-1 pb-1 pb-lg-2 pb-xl-3">
                    <div class="d-flex gap-1 text-warning mb-3">
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                    </div>
                    <span class="h5 pb-2 mb-1"><?= t('rev.r1p', 'First-class extracts.') ?></span>
                    <p class="mt-2"><?= t('rev.r1', 'High-quality cooperation. First-class extracts. Fast and customer-oriented communication and quick delivery of goods. We highly recommend them.') ?></p>
                  </div>
                  <div class="card-footer position-relative z-1 d-flex align-items-center bg-transparent border-0 py-4">
                    <div class="ratio ratio-1x1 flex-shrink-0 bg-body-secondary rounded-circle overflow-hidden" style="width: 44px">
                      <img src="/x-assets/img/home/single-product/reviews/01-review-oktarina.png" alt="Avatar">
                    </div>
                    <div class="fs-sm ps-2 ms-1">
                      <div class="fw-semibold text-dark-emphasis">Tereza R.</div>
                      <div>Oktarina Syrups</div>
                    </div>
                  </div>
                  <span class="position-absolute top-0 start-0 w-100 h-100 bg-white d-none-dark"></span>
                  <span class="position-absolute top-0 start-0 w-100 h-100 bg-white d-none d-block-dark" style="opacity: .08"></span>
                </div>

                <!-- Review -->
                <div class="card w-100 bg-transparent border-0 rounded-5 overflow-hidden p-xl-2">
                  <div class="card-body position-relative z-1 pb-1 pb-lg-2 pb-xl-3">
                    <div class="d-flex gap-1 text-warning mb-3">
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                    </div>
                    <span class="h5 pb-2 mb-1"><?= t('rev.r2p', 'Production of "eau de vie" that rivals the best brands.') ?></span>
                    <p class="mt-2"><?= t('rev.r2', 'I am extremely satisfied with the products I purchased. The flavors are authentic and the mixing ratios are perfect. I highly recommend them to new buyers, especially the Williams pear, which I used to make an eau de vie that rivals the best brands.') ?></p>
                  </div>
                  <div class="card-footer position-relative z-1 d-flex align-items-center bg-transparent border-0 py-4">
                    <div class="ratio ratio-1x1 flex-shrink-0 bg-body-secondary rounded-circle overflow-hidden" style="width: 44px">
                      <img src="/x-assets/img/home/single-product/reviews/02-review-gregoire.png" alt="Avatar">
                    </div>
                    <div class="fs-sm ps-2 ms-1">
                      <div class="fw-semibold text-dark-emphasis">Alain Gregoire</div>
                      <div></div>
                    </div>
                  </div>
                  <span class="position-absolute top-0 start-0 w-100 h-100 bg-white d-none-dark"></span>
                  <span class="position-absolute top-0 start-0 w-100 h-100 bg-white d-none d-block-dark" style="opacity: .08"></span>
                </div>
              </div>
              <div class="col-lg-4 d-flex flex-column flex-md-row flex-lg-column gap-4">

                <!-- Review -->
                <div class="card w-100 bg-transparent border-0 rounded-5 overflow-hidden p-xl-2">
                  <div class="card-body position-relative z-1 pb-1 pb-lg-2 pb-xl-3">
                    <div class="d-flex gap-1 text-warning mb-3">
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                    </div>
                    <span class="h5 pb-2 mb-1"><?= t('rev.r3p', 'Great prices and excellent aroma quality.') ?></span>
                    <p class="mt-2"><?= t('rev.r3', 'Excellent product prices, outstanding aroma quality, fast order processing and delivery – 2 days. I am extremely satisfied and will definitely shop here again. I highly recommend this company 🙂') ?></p>
                  </div>
                  <div class="card-footer position-relative z-1 d-flex align-items-center bg-transparent border-0 py-4">
                    <div class="ratio ratio-1x1 flex-shrink-0 bg-body-secondary rounded-circle overflow-hidden" style="width: 44px">
                      <img src="/x-assets/img/home/single-product/reviews/03-review-uli.png" alt="Avatar">
                    </div>
                    <div class="fs-sm ps-2 ms-1">
                      <div class="fw-semibold text-dark-emphasis">Rasto Uli</div>
                      <div></div>
                    </div>
                  </div>
                  <span class="position-absolute top-0 start-0 w-100 h-100 bg-white d-none-dark"></span>
                  <span class="position-absolute top-0 start-0 w-100 h-100 bg-white d-none d-block-dark" style="opacity: .08"></span>
                </div>

                <!-- Review -->
                <div class="card w-100 bg-transparent border-0 rounded-5 overflow-hidden p-xl-2">
                  <div class="card-body position-relative z-1 pb-1 pb-lg-2 pb-xl-3">
                    <div class="d-flex gap-1 text-warning mb-3">
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                    </div>
                    <span class="h5 pb-2 mb-1"><?= t('rev.r4p', 'Super intense cravings.') ?></span>
                    <p class="mt-2"><?= t('rev.r4', 'web') ?></p>
                  </div>
                  <div class="card-footer position-relative z-1 d-flex align-items-center bg-transparent border-0 py-4">
                    <div class="ratio ratio-1x1 flex-shrink-0 bg-body-secondary rounded-circle overflow-hidden" style="width: 44px">
                      <img src="/x-assets/img/home/single-product/reviews/04-review-tom.png" alt="Avatar">
                    </div>
                    <div class="fs-sm ps-2 ms-1">
                      <div class="fw-semibold text-dark-emphasis">Tom H.</div>
                      <div></div>
                    </div>
                  </div>
                  <span class="position-absolute top-0 start-0 w-100 h-100 bg-white d-none-dark"></span>
                  <span class="position-absolute top-0 start-0 w-100 h-100 bg-white d-none d-block-dark" style="opacity: .08"></span>
                </div>
              </div>
              <div class="col-lg-4 d-flex flex-column flex-md-row flex-lg-column gap-4">

                <!-- Review -->
                <div class="card w-100 bg-transparent border-0 rounded-5 overflow-hidden p-xl-2">
                  <div class="card-body position-relative z-1 pb-1 pb-lg-2 pb-xl-3">
                    <div class="d-flex gap-1 text-warning mb-3">
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                    </div>
                    <span class="h5 pb-2 mb-1"><?= t('rev.r5p', 'I have been a customer for several years.') ?></span>
                    <p class="mt-2"><?= t('rev.r5', 'We have been a ZAN-AROMI customer for several years and greatly appreciate our cooperation. Customer support is very professional and inquiries are handled very quickly. The quality of the products speaks for itself. Keep up the good work and look forward to many more years together!') ?></p>
                  </div>
                  <div class="card-footer position-relative z-1 d-flex align-items-center bg-transparent border-0 py-4">
                    <div class="ratio ratio-1x1 flex-shrink-0 bg-body-secondary rounded-circle overflow-hidden" style="width: 44px">
                      <img src="/x-assets/img/home/single-product/reviews/05-review-knotz.png" alt="Avatar">
                    </div>
                    <div class="fs-sm ps-2 ms-1">
                      <div class="fw-semibold text-dark-emphasis">Patrick Krotz</div>
                      <div></div>
                    </div>
                  </div>
                  <span class="position-absolute top-0 start-0 w-100 h-100 bg-white d-none-dark"></span>
                  <span class="position-absolute top-0 start-0 w-100 h-100 bg-white d-none d-block-dark" style="opacity: .08"></span>
                </div>

                <!-- Review -->
                <div class="card w-100 bg-transparent border-0 rounded-5 overflow-hidden p-xl-2">
                  <div class="card-body position-relative z-1 pb-1 pb-lg-2 pb-xl-3">
                    <div class="d-flex gap-1 text-warning mb-3">
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                      <i class="ci-star-filled"></i>
                    </div>
                    <span class="h5 pb-2 mb-1"><?= t('rev.r6p', 'The products surpass everything in taste and naturalness.') ?></span>
                    <p class="mt-2"><?= t('rev.r6', 'Hello, I have tried many flavors from other manufacturers, but ZAN-AROMI products surpass everything in taste and naturalness. I will order from here again and again.') ?></p>
                  </div>
                  <div class="card-footer position-relative z-1 d-flex align-items-center bg-transparent border-0 py-4">
                    <div class="ratio ratio-1x1 flex-shrink-0 bg-body-secondary rounded-circle overflow-hidden" style="width: 44px">
                      <img src="/x-assets/img/home/single-product/reviews/06-review-schantin.png" alt="Avatar">
                    </div>
                    <div class="fs-sm ps-2 ms-1">
                      <div class="fw-semibold text-dark-emphasis">Reinhard Schantin</div>
                      <div></div>
                    </div>
                  </div>
                  <span class="position-absolute top-0 start-0 w-100 h-100 bg-white d-none-dark"></span>
                  <span class="position-absolute top-0 start-0 w-100 h-100 bg-white d-none d-block-dark" style="opacity: .08"></span>
                </div>


                <!-- Video review
                <div class="position-relative w-100 rounded-5 overflow-hidden">
                  <div class="d-lg-none" style="height: 300px"></div>
                  <div class="d-none d-lg-block" style="height: 364px"></div>
                  <div class="position-absolute top-0 start-0 w-100 h-100 z-3 p-4">
                    <a class="btn btn-lg btn-light stretched-link rounded-pill mt-xl-2 ms-xl-2" href="https://www.youtube.com/watch?v=ME5CirMkFZE" data-glightbox data-gallery="video2">
                      <i class="ci-play fs-lg me-2 ms-n1"></i>
                      Play
                    </a>
                  </div>
                  <div class="position-absolute top-0 start-0 d-flex align-items-end w-100 h-100 z-2 p-4">
                    <div class="mb-xl-2 ms-xl-2">
                      <div class="d-flex gap-1 text-warning mb-3">
                        <i class="ci-star-filled"></i>
                        <i class="ci-star-filled"></i>
                        <i class="ci-star-filled"></i>
                        <i class="ci-star-filled"></i>
                        <i class="ci-star-filled"></i>
                      </div>
                      <h3 class="h5 text-white mb-0">Keeps drinks cold for hours</h3>
                    </div>
                  </div>
                  <span class="position-absolute top-0 start-0 w-100 h-100 z-1" style="background: linear-gradient(180deg, rgba(255, 255, 255, 0.00) 0%, rgba(0, 0, 0, 0.50) 100%)"></span>
                  <img src="/x-assets/img/home/single-product/reviews/video.jpg" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover" alt="Image">
                </div>
                 -->
              </div>
            </div>
          </div>

          <!-- Background color -->
          <div class="position-absolute top-0 start-0 w-100 h-100 d-none-dark" style="background: linear-gradient(90deg, #a8d8ff 0%, #7fb8f0 100%);
            border-top-right-radius: 48px; border-top-left-radius: 48px"></div>
          <div class="position-absolute top-0 start-0 w-100 h-100 d-none d-block-dark" style="background: linear-gradient(119deg, #333126 0%, #372e2f 52.24%); border-top-right-radius: 48px; border-top-left-radius: 48px"></div>
        </div>
      </section>



      <!-- Blog grid 
      <section class="container py-5 my-2 my-sm-3 my-lg-4 my-xl-5">
-->
        <!-- Heading 
        <div class="d-flex align-items-center justify-content-between pb-3 mb-2 mb-sm-3 mt-xxl-3">
          <h2 class="h3 mb-0">Blog and news</h2>
          <div class="nav ms-3">
            <a class="nav-link animate-underline px-0 py-2" href="blog-grid-v2.html">
              <span class="animate-target">View all</span>
              <i class="ci-chevron-right fs-base ms-1"></i>
            </a>
          </div>
        </div>

        <div class="row gy-5 mb-xxl-3">
-->
          <!-- Article 
          <article class="col-md-6">
            <a class="ratio d-flex hover-effect-scale rounded-4 overflow-hidden" href="#!" style="--cz-aspect-ratio: calc(500 / 636 * 100%)">
              <img src="/x-assets/img/blog/grid/v2/01.jpg" class="hover-effect-target" alt="Image">
            </a>
            <div class="pt-4">
              <div class="nav pb-2 mb-1">
                <a class="nav-link text-body fs-xs text-uppercase p-0" href="#!">Interior design</a>
              </div>
              <h3 class="h5 mb-3">
                <a class="hover-effect-underline" href="#!">Decorate your home for the festive season in 3 easy steps</a>
              </h3>
              <div class="nav align-items-center gap-2 fs-xs">
                <a class="nav-link text-body-secondary fs-xs fw-normal p-0" href="#!">Ava Johnson</a>
                <hr class="vr my-1 mx-1">
                <span class="text-body-secondary">September 11, 2024</span>
              </div>
            </div>
          </article>
          <div class="col-md-6">
            <div class="row row-cols-1 row-cols-sm-2 gy-5">
-->
              <!-- Article 
              <article class="col">
                <a class="ratio d-flex hover-effect-scale rounded-4 overflow-hidden" href="#!" style="--cz-aspect-ratio: calc(260 / 306 * 100%)">
                  <img src="/x-assets/img/blog/grid/v2/11.jpg" class="hover-effect-target" alt="Image">
                </a>
                <div class="pt-4">
                  <div class="nav pb-2 mb-1">
                    <a class="nav-link text-body fs-xs text-uppercase p-0" href="#!">Interior design</a>
                  </div>
                  <h3 class="h6 mb-3">
                    <a class="hover-effect-underline" href="#!">Transform your living space with these chic interior design tips</a>
                  </h3>
                  <div class="nav align-items-center gap-2 fs-xs">
                    <a class="nav-link text-body-secondary fs-xs fw-normal p-0" href="#!">Ethan Miller</a>
                    <hr class="vr my-1 mx-1">
                    <span class="text-body-secondary">September 5, 2024</span>
                  </div>
                </div>
              </article>
-->
              <!-- Article 
              <article class="col">
                <a class="ratio d-flex hover-effect-scale rounded-4 overflow-hidden" href="#!" style="--cz-aspect-ratio: calc(260 / 306 * 100%)">
                  <img src="/x-assets/img/blog/grid/v2/10.jpg" class="hover-effect-target" alt="Image">
                </a>
                <div class="pt-4">
                  <div class="nav pb-2 mb-1">
                    <a class="nav-link text-body fs-xs text-uppercase p-0" href="#!">Furniture</a>
                  </div>
                  <h3 class="h6 mb-3">
                    <a class="hover-effect-underline" href="#!">Furnishing your space: a guide to choosing the perfect furniture pieces</a>
                  </h3>
                  <div class="nav align-items-center gap-2 fs-xs">
                    <a class="nav-link text-body-secondary fs-xs fw-normal p-0" href="#!">Oliver Harris</a>
                    <hr class="vr my-1 mx-1">
                    <span class="text-body-secondary">August 23, 2024</span>
                  </div>
                </div>
              </article>
            </div>
          </div>
        </div>
      </section>
    </main>
-->

    <!-- Page footer 
    <footer class="footer bg-dark pb-4 py-lg-5" data-bs-theme="dark">
      <div class="container pt-5 pt-lg-4 mt-sm-2 mt-md-3">
        <div class="row pb-5">
-->
          <!-- Subscription + Social account links 
          <div class="col-md col-xl-8 order-md-2">
            <div class="text-center px-sm-4 mx-auto" style="max-width: 568px">
              <h3 class="pb-1 mb-2">Stay in touch with us</h3>
              <p class="fs-sm text-body pb-2 pb-sm-3">Receive the latest updates about our products &amp; promotions</p>
              <form class="needs-validation position-relative" novalidate>
                <input type="email" class="form-control form-control-lg rounded-pill text-start" placeholder="You email" aria-label="Your email address" required>
                <div class="invalid-tooltip bg-transparent p-0">Please enter you email address!</div>
                <button type="submit" class="btn btn-icon fs-xl btn-dark rounded-circle position-absolute top-0 end-0 mt-1 me-1" aria-label="Submit your email address" data-bs-theme="light">
                  <i class="ci-arrow-up-right"></i>
                </button>
              </form>
              <div class="d-flex justify-content-center gap-2 pt-4 pt-md-5 mt-1 mt-md-0">
                <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#!" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-white p-0"></div></div>' title="YouTube" aria-label="Follow us on YouTube">
                  <i class="ci-youtube"></i>
                </a>
                <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#!" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-white p-0"></div></div>' title="Facebook" aria-label="Follow us on Facebook">
                  <i class="ci-facebook"></i>
                </a>
                <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#!" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-white p-0"></div></div>' title="Instagram" aria-label="Follow us on Instagram">
                  <i class="ci-instagram"></i>
                </a>
                <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#!" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-white p-0"></div></div>' title="Telegram" aria-label="Follow us on Telegram">
                  <i class="ci-telegram"></i>
                </a>
                <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#!" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-white p-0"></div></div>' title="Pinterest" aria-label="Follow us on Pinterest">
                  <i class="ci-pinterest"></i>
                </a>
              </div>
            </div>
          </div>
-->
          <!-- Category links 
          <div class="col-md-auto col-xl-2 text-center order-md-1 pt-4 pt-md-0">
            <ul class="nav d-inline-flex flex-md-column justify-content-center align-items-center gap-md-2">
              <li class="animate-underline my-1 mx-2 m-md-0">
                <a class="nav-link d-inline-flex fw-normal p-0 animate-target" href="#!">Bedroom</a>
              </li>
              <li class="animate-underline my-1 mx-2 m-md-0">
                <a class="nav-link d-inline-flex fw-normal p-0 animate-target" href="#!">Living room</a>
              </li>
              <li class="animate-underline my-1 mx-2 m-md-0">
                <a class="nav-link d-inline-flex fw-normal p-0 animate-target" href="#!">Bathroom</a>
              </li>
              <li class="animate-underline my-1 mx-2 m-md-0">
                <a class="nav-link d-inline-flex fw-normal p-0 animate-target" href="#!">Decoration</a>
              </li>
              <li class="animate-underline my-1 mx-2 m-md-0">
                <a class="nav-link d-inline-flex fw-normal p-0 animate-target" href="#!">Kitchen</a>
              </li>
              <li class="animate-underline my-1 mx-2 m-md-0">
                <a class="nav-link d-inline-flex fw-normal p-0 animate-target" href="#!">Sale</a>
              </li>
            </ul>
          </div>
-->
          <!-- Customer links 
          <div class="col-md-auto col-xl-2 text-center order-md-3 pt-3 pt-md-0">
            <ul class="nav d-inline-flex flex-md-column justify-content-center align-items-center gap-md-2">
              <li class="animate-underline my-1 mx-2 m-md-0">
                <a class="nav-link d-inline-flex fw-normal p-0 animate-target" href="#!">Shipping options</a>
              </li>
              <li class="animate-underline my-1 mx-2 m-md-0">
                <a class="nav-link d-inline-flex fw-normal p-0 animate-target" href="#!">Tracking a package</a>
              </li>
              <li class="animate-underline my-1 mx-2 m-md-0">
                <a class="nav-link d-inline-flex fw-normal p-0 animate-target" href="#!">Help center</a>
              </li>
              <li class="animate-underline my-1 mx-2 m-md-0">
                <a class="nav-link d-inline-flex fw-normal p-0 animate-target" href="#!">Contact us</a>
              </li>
              <li class="animate-underline my-1 mx-2 m-md-0">
                <a class="nav-link d-inline-flex fw-normal p-0 animate-target" href="#!">Product returns</a>
              </li>
              <li class="animate-underline my-1 mx-2 m-md-0">
                <a class="nav-link d-inline-flex fw-normal p-0 animate-target" href="#!">Locations</a>
              </li>
            </ul>
          </div>
        </div>
-->
        <!-- Copyright 
        <p class="fs-xs text-body text-center pt-lg-4 mt-n2 mt-md-0 mb-0">
          &copy; All rights reserved. Made by <span class="animate-underline"><a class="animate-target text-white text-decoration-none" href="https://coderthemes.com/" target="_blank" rel="noreferrer">Coderthemes</a></span>
        </p>
      </div>
    </footer>
-->




              <!-- Pagination (Bullets) -->
              <div class="swiper-pagination position-static pt-3 mt-sm-1 mt-md-2 mt-lg-3"></div>
            </div>
          </div>
        </div>
      </section>


      <!-- Instagram feed -->
      <section class="container pt-5 mt-1 mt-sm-2 mt-md-3 mt-lg-4 mt-xl-5">
        <div class="text-center pt-xxl-3 pb-2 pb-md-3">
          <h2 class="pb-2 mb-1">
            <span class="animate-underline">
              <a class="animate-target text-dark-emphasis text-decoration-none" href="https://www.instagram.com/zanaromicom">#zanaromicom</a>
            </span>
          </h2>
          <p><?= t('inst.p', 'Find more inspiration on our Instagram') ?></p>
        </div>
        <div class="overflow-x-auto pb-3 mb-n3" data-simplebar>
          <div class="d-flex gap-2 gap-md-3 gap-lg-4" style="min-width: 700px">
            <a class="hover-effect-scale hover-effect-opacity position-relative w-100 overflow-hidden" href="">
              <span class="hover-effect-target position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-25 opacity-0 z-1"></span>
              <i class="ci-instagram hover-effect-target fs-4 text-white position-absolute top-50 start-50 translate-middle opacity-0 z-2"></i>
              <div class="hover-effect-target ratio ratio-1x1">
                <img src="/x-assets/img/instagram/1.png" alt="Instagram image">
              </div>
            </a>
            <a class="hover-effect-scale hover-effect-opacity position-relative w-100 overflow-hidden" href="">
              <span class="hover-effect-target position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-25 opacity-0 z-1"></span>
              <i class="ci-instagram hover-effect-target fs-4 text-white position-absolute top-50 start-50 translate-middle opacity-0 z-2"></i>
              <div class="hover-effect-target ratio ratio-1x1">
                <img src="/x-assets/img/instagram/2.png" alt="Instagram image">
              </div>
            </a>
            <a class="hover-effect-scale hover-effect-opacity position-relative w-100 overflow-hidden" href="">
              <span class="hover-effect-target position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-25 opacity-0 z-1"></span>
              <i class="ci-instagram hover-effect-target fs-4 text-white position-absolute top-50 start-50 translate-middle opacity-0 z-2"></i>
              <div class="hover-effect-target ratio ratio-1x1">
                <img src="/x-assets/img/instagram/3.png" alt="Instagram image">
              </div>
            </a>
            <a class="hover-effect-scale hover-effect-opacity position-relative w-100 overflow-hidden" href="">
              <span class="hover-effect-target position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-25 opacity-0 z-1"></span>
              <i class="ci-instagram hover-effect-target fs-4 text-white position-absolute top-50 start-50 translate-middle opacity-0 z-2"></i>
              <div class="hover-effect-target ratio ratio-1x1">
                <img src="/x-assets/img/instagram/4.png" alt="Instagram image">
              </div>
            </a>
            <a class="hover-effect-scale hover-effect-opacity position-relative w-100 overflow-hidden" href="">
              <span class="hover-effect-target position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-25 opacity-0 z-1"></span>
              <i class="ci-instagram hover-effect-target fs-4 text-white position-absolute top-50 start-50 translate-middle opacity-0 z-2"></i>
              <div class="hover-effect-target ratio ratio-1x1">
                <img src="/x-assets/img/instagram/5.png" alt="Instagram image">
              </div>
            </a>
          </div>
        </div>
      </section>
    </main>


<!-- Page footer -->
    <footer class="footer pt-5 pb-4">
      <div class="container pt-sm-2 pt-md-3 pt-lg-4">
        <div class="row pb-5 mb-lg-3">

          <!-- Columns with links that are turned into accordion on screens < 500px wide (sm breakpoint) -->
          <div class="col-md-8 col-xl-7 pb-2 pb-md-0 mb-4 mb-md-0 mt-n3 mt-sm-0">
            <div class="accordion" id="footerLinks">
              <div class="row row-cols-1 row-cols-sm-3">
                <div class="accordion-item col border-0">
                  <div class="accordion-header" id="categoriesHeading">
                    <span class="fw-semibold text-dark-emphasis d-none d-sm-block"><?= t('foot.s1h', 'Company') ?></span>
                    <button type="button" class="accordion-button collapsed py-3 d-sm-none" data-bs-toggle="collapse" data-bs-target="#categoriesLinks" aria-expanded="false" aria-controls="categoriesLinks">Company</button>
                  </div>
                  <div class="accordion-collapse collapse d-sm-block" id="categoriesLinks" aria-labelledby="categoriesHeading" data-bs-parent="#footerLinks">
                    <ul class="nav flex-column gap-2 pt-sm-3 pb-3 pb-sm-0 mt-n1 mb-1 mb-sm-0">
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-target d-inline fw-normal text-truncate p-0" href="">ZAN-AROMI, spol. s r.o.</a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-target d-inline fw-normal text-truncate p-0" href=""><?= t('foot.s12', 'Turisticka 8/7') ?></a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-target d-inline fw-normal text-truncate p-0" href="">62100 Brno</a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-target d-inline fw-normal text-truncate p-0" href=""><?= t('foot.s14', 'Czech Republic') ?></a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="tel:+420603143585">+420&nbsp;603&nbsp;143&nbsp;585</a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="mailto:info@zanaromi.com">info@zanaromi.com</a>
                      </li>
                    </ul>
                  </div>
                  <hr class="d-sm-none my-0">
                </div>
                <div class="accordion-item col border-0">
                  <div class="accordion-header" id="accountHeading">
                    <span class="fw-semibold text-dark-emphasis d-none d-sm-block"><?= t('foot.s2h', 'Account') ?></span>
                    <button type="button" class="accordion-button collapsed py-3 d-sm-none" data-bs-toggle="collapse" data-bs-target="#accountLinks" aria-expanded="false" aria-controls="accountLinks">Account</button>
                  </div>
                  <div class="accordion-collapse collapse d-sm-block" id="accountLinks" aria-labelledby="accountHeading" data-bs-parent="#footerLinks">
                    <ul class="nav flex-column gap-2 pt-sm-3 pb-3 pb-sm-0 mt-n1 mb-1 mb-sm-0">
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/<?= t('url.guest_tracking', 'guest-tracking') ?>"><?= t('foot.s21', 'Order tracking') ?></a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/<?= t('url.my_account', 'my-account') ?>"><?= t('foot.s22', 'Sign in') ?></a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/<?= t('url.create_account', 'create-account') ?>"><?= t('foot.s23', 'Create account') ?></a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/content/7-privacy-policy-gdpr"><?= t('foot.s24', 'Cookies') ?></a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/134-new-arrivals-new"><?= t('foot.s25', 'New arrivals') ?></a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/132-hot-of-the-day"><?= t('foot.s26', 'Discounts') ?></a>
                      </li>
                    </ul>
                  </div>
                  <hr class="d-sm-none my-0">
                </div>
                <div class="accordion-item col border-0">
                  <div class="accordion-header" id="customerHeading">
                    <span class="fw-semibold text-dark-emphasis d-none d-sm-block"><?= t('foot.s3h', 'Customer service') ?></span>
                    <button type="button" class="accordion-button collapsed py-3 d-sm-none" data-bs-toggle="collapse" data-bs-target="#customerLinks" aria-expanded="false" aria-controls="customerLinks">Customer service</button>
                  </div>
                  <div class="accordion-collapse collapse d-sm-block" id="customerLinks" aria-labelledby="customerHeading" data-bs-parent="#footerLinks">
                    <ul class="nav flex-column gap-2 pt-sm-3 pb-3 pb-sm-0 mt-n1 mb-1 mb-sm-0">
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/content/3-terms-and-conditions-of-use"><?= t('foot.s31', 'Terms and Conditions') ?></a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/content/4-about-us"><?= t('foot.s32', 'About Us') ?></a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/content/7-privacy-policy-gdpr"><?= t('foot.s33', 'Privacy Policy GDPR') ?></a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/content/8-withdrawal-form"><?= t('foot.s34', 'Withdrawal Form') ?></a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/shop/<?= $lang ?>/<?= t('url.contact', 'contact-us') ?>"><?= t('foot.s35', 'Contact us') ?></a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/<?= $lang ?>/sitemap"><?= t('foot.s36', 'Sitemap') ?></a>
                      </li>
                    </ul>
                  </div>
                  <hr class="d-sm-none my-0">
                </div>
              </div>
            </div>
          </div>


<!-- Subscription -->
<div class="col-md-4 offset-xl-1">
  <div class="fw-semibold text-dark-emphasis d-none d-sm-block mb-4"><?= t('foot.s4h', 'Join us and stay up to date') ?></div>

  <form id="ps-nl-form" class="needs-validation" novalidate>
    <div class="form-check form-check-inline">
      <input type="checkbox" class="form-check-input" id="check-woman" name="topic[]" value="flavors" checked>
      <label for="check-woman" class="form-check-label"><?= t('foot.s41a', 'Flavors') ?></label>
    </div>
    <div class="form-check form-check-inline">
      <input type="checkbox" class="form-check-input" id="check-man" name="topic[]" value="oenology">
      <label for="check-man" class="form-check-label"><?= t('foot.s41b', 'Oenology') ?></label>
    </div>

    <div class="position-relative mt-3">
      <input type="email" class="form-control form-control-lg bg-image-none text-start"
             name="email" placeholder="Enter email" aria-label="Your email address" required>
      <div class="invalid-tooltip bg-transparent p-0"><?= t('foot.s42', 'Please enter your email address!') ?></div>

      <button type="submit"
              class="btn btn-icon btn-ghost fs-xl btn-secondary border-0 position-absolute top-0 end-0 mt-1 me-1"
              aria-label="Submit your email address">
        <i class="ci-arrow-up-right"></i>
      </button>
    </div>

<!-- Místo pro zprávu -->
    <small id="ps-nl-msg" class="d-block mt-2"></small>

<!-- Social account links -->
              <div class="d-flex justify-content-center justify-content-lg-start gap-2 mt-n2 mt-md-3">
                <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="https://www.youtube.com/channel/UCEWha4TaZnzu58HhUFloo3g" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-body p-0"></div></div>' title="YouTube" aria-label="Follow us on YouTube">
                  <i class="ci-youtube"></i>
                </a>
                <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="https://www.facebook.com/zanaromicom" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-body p-0"></div></div>' title="Facebook" aria-label="Follow us on Facebook">
                  <i class="ci-facebook"></i>
                </a>
                <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="https://www.instagram.com/zanaromicom" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-body p-0"></div></div>' title="Instagram" aria-label="Follow us on Instagram">
                  <i class="ci-instagram"></i>
                </a>
                <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="https://t.me/zanaromicom" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-body p-0"></div></div>' title="Telegram" aria-label="Follow us on Telegram">
                  <i class="ci-telegram"></i>
                </a>
                <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="https://wa.me/420603143585" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-body p-0"></div></div>' title="Whatsapp" aria-label="Follow us on Whatsapp">
                  <i class="ci-whatsapp"></i>
                </a>
                <!--
                <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#!" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-body p-0"></div></div>' title="Pinterest" aria-label="Follow us on Pinterest">
                  <i class="ci-pinterest"></i>
                </a>
                -->
              </div>


  
  </form>
</div>



<script>
(function() {
  const ENDPOINT = 'https://zanaromi.com/shop/index.php?fc=module&module=ps_emailsubscription&controller=subscription';

  const form = document.getElementById('ps-nl-form');
  const msgEl = document.getElementById('ps-nl-msg');

  if (!form || !msgEl) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!form.checkValidity()) {
      form.classList.add('was-validated');
      return;
    }

    const fd = new FormData(form);

    // povinné / běžné parametry
    fd.set('submitNewsletter', '1');
    fd.set('ajax', '1');

    // action: zkus buď smazat, nebo dát 0
    // fd.delete('action');
    fd.set('action', '0');

    msgEl.textContent = 'Sending…';
    msgEl.classList.remove('text-danger','text-success');

    try {
      const res = await fetch(ENDPOINT, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        credentials: 'same-origin',
        body: fd
      });

      const text = await res.text();
      let data = {};
      try { data = JSON.parse(text); } catch (_) {}

      const ok =
        data.success === true ||
        data.nw_error === false ||
        data.nw_error === 0;

      const msg =
        data.msg ||
        (ok ? 'Thank you for subscribing.' : 'Subscription failed. Please try again.');

      msgEl.textContent = msg;
      msgEl.classList.add(ok ? 'text-success' : 'text-danger');

      if (ok) {
        form.reset();
        form.classList.remove('was-validated');
      } else {
        // Debug pomoc: když server vrátil HTML, uvidíš to hned v konzoli
        if (!Object.keys(data).length) console.warn('Non-JSON response:', text.slice(0, 500));
      }
    } catch (err) {
      msgEl.textContent = 'Connection error. Please try again.';
      msgEl.classList.add('text-danger');
      console.error(err);
    }
  }, false);
})();
</script>



              

            </form>
          </div>


        <!-- Social account links 
        <div class="d-flex justify-content-center justify-content-lg-start gap-2 mt-n2 mt-md-0">
          <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="https://www.youtube.com/channel/UCEWha4TaZnzu58HhUFloo3g" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-body p-0"></div></div>' title="YouTube" aria-label="Follow us on YouTube">
            <i class="ci-youtube"></i>
          </a>
          <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="https://www.facebook.com/zanaromicom" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-body p-0"></div></div>' title="Facebook" aria-label="Follow us on Facebook">
            <i class="ci-facebook"></i>
          </a>
          <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="https://www.instagram.com/zanaromicom" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-body p-0"></div></div>' title="Instagram" aria-label="Follow us on Instagram">
            <i class="ci-instagram"></i>
          </a>
          <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="https://t.me/zanaromicom" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-body p-0"></div></div>' title="Telegram" aria-label="Follow us on Telegram">
            <i class="ci-telegram"></i>
          </a>
          <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="https://wa.me/420603143585" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-body p-0"></div></div>' title="Whatsapp" aria-label="Follow us on Whatsapp">
            <i class="ci-whatsapp"></i>
          </a>
          -->
          <!--
          <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#!" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-body p-0"></div></div>' title="Pinterest" aria-label="Follow us on Pinterest">
            <i class="ci-pinterest"></i>
          </a>
          
        </div>
-->

        <!-- Copyright + Payment methods -->
        <div class="d-lg-flex align-items-center border-top pt-4 mt-3">
          <div class="d-flex gap-2 gap-sm-3 justify-content-center ms-lg-auto mb-3 mb-md-4 mb-lg-0 order-lg-2">
            <div>
              <img src="/x-assets/img/payment-methods/gopay-colorfull2.svg" class="d-none-dark" alt="GoPay">
              <img src="/x-assets/img/payment-methods/gopay-colorfull2.svg" class="d-none d-block-dark" alt="GoPay">
            </div>
            <div>
              <img src="/x-assets/img/payment-methods/visa-light-mode.svg" class="d-none-dark" alt="Visa">
              <img src="/x-assets/img/payment-methods/visa-dark-mode.svg" class="d-none d-block-dark" alt="Visa">
            </div>
            <!--
            <div>
              <img src="/x-assets/img/payment-methods/paypal-light-mode.svg" class="d-none-dark" alt="PayPal">
              <img src="/x-assets/img/payment-methods/paypal-dark-mode.svg" class="d-none d-block-dark" alt="PayPal">
            </div>
            -->
            <div>
              <img src="/x-assets/img/payment-methods/mastercard.svg" alt="Mastercard">
            </div>
            <div>
              <img src="/x-assets/img/payment-methods/google-pay-light-mode.svg" class="d-none-dark" alt="Google Pay">
              <img src="/x-assets/img/payment-methods/google-pay-dark-mode.svg" class="d-none d-block-dark" alt="Google Pay">
            </div>
            <div>
              <img src="/x-assets/img/payment-methods/apple-pay-light-mode.svg" class="d-none-dark" alt="Apple Pay">
              <img src="/x-assets/img/payment-methods/apple-pay-dark-mode.svg" class="d-none d-block-dark" alt="Apple Pay">
            </div>
          </div>
          <div class="d-md-flex justify-content-center order-lg-1">
            <ul class="nav justify-content-center gap-4 order-md-3 mb-4 mb-md-0">
              <li class="animate-underline">
                <a class="nav-link fs-xs fw-normal p-0 animate-target" href="/shop/<?= $lang ?>/content/7-privacy-policy-gdpr"><?= t('foot.copy1', 'Privacy') ?></a>
              </li>
              <li class="animate-underline">
                <a class="nav-link fs-xs fw-normal p-0 animate-target" href="/<?= $lang ?>/"><?= t('foot.copy2', 'Affiliates') ?></a>
              </li>
              <li class="animate-underline">
                <a class="nav-link fs-xs fw-normal p-0 animate-target" href="/shop/<?= $lang ?>/content/3-terms-and-conditions-of-use"><?= t('foot.copy3', 'Terms of use') ?></a>
              </li>
            </ul>
            <div class="vr text-body-secondary opacity-25 mx-4 d-none d-md-inline-block order-md-2"></div>
            <p class="fs-xs text-center text-lg-start mb-0 order-md-1">
              &copy; <?= t('foot.copy4', 'All rights reserved. Made by') ?> <span class="animate-underline"><a class="animate-target text-dark-emphasis text-decoration-none" href="mailto:mfink@email.cz" target="_blank" rel="noreferrer">mikmikecom</a></span>
            </p>
          </div>
        </div>
      </div>
    </footer>


    <!-- Back to top button -->
    <div class="floating-buttons position-fixed top-50 end-0 z-sticky me-3 me-xl-4 pb-4">
      <a class="btn-scroll-top btn btn-sm bg-body border-0 rounded-pill shadow animate-slide-end" href="#top">
        <?= t('top.b', 'Top') ?>
        <i class="ci-arrow-right fs-base ms-1 me-n1 animate-target"></i>
        <span class="position-absolute top-0 start-0 w-100 h-100 border rounded-pill z-0"></span>
        <svg class="position-absolute top-0 start-0 w-100 h-100 z-1" viewBox="0 0 62 32" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x=".75" y=".75" width="60.5" height="30.5" rx="15.25" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10"/>
        </svg>
      </a>
    </div>


    <!-- Vendor scripts -->
    <script src="/x-assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="/x-assets/vendor/simplebar/dist/simplebar.min.js"></script>
    <!--
    <script src="/x-assets/vendor/glightbox/dist/js/glightbox.min.js"></script>
    -->
    <!-- Bootstrap + Theme scripts -->
    <script src="/x-assets/js/theme.min.js"></script>
  </body>
</html>
