<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover"/>
  <meta name="description" content="BISLIG iCENTER - The No.#1 Supplier of iPhones in Mindanao. Shop now for the latest iPhones!"/>
  <title>BiSLIG iCENTER</title>
    <link rel="icon" type="image/png" href="images/iCenter.png">
  <link rel="shortcut icon" type="image/png" href="images/iCenter.png">
  <link rel="apple-touch-icon" href="images/iCenter.png">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #000 60%, #003344 100%);
      color: #fff;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      align-items: center;
      height: 100vh;
      height: 100dvh; /* Dynamic viewport height for mobile */
      overflow: hidden;
      text-align: center;
      /* overflow: hidden; */  /* Removed to allow scrolling */
      position: relative;
      perspective: 1000px;
      /* Prevent zoom on double tap for iOS */
      touch-action: manipulation;
      -webkit-touch-callout: none;
      -webkit-user-select: none;
      -khtml-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
      user-select: none;
    }
    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at center, transparent 0%, rgba(0,0,0,0.5) 100%);
      z-index: 1;
    }
    main {
      position: relative;
      z-index: 2;
      animation: fadeIn 1.5s ease-out;
      transform-style: preserve-3d;
      animation: floatIn 2s ease-out;
      flex-grow: 1;
      width: 100%;
      overflow: auto;
    }
    h1 {
      font-family: 'Times New Roman', Times, serif;
      font-size: 4rem;
      margin-bottom: 0.5rem;
      font-weight: 900;
      letter-spacing: 4px;
      text-transform: uppercase;
      text-shadow: 0 0 10px rgba(0,170,255,0.5);
      animation: slideDown 1s ease-out;
      transform-style: preserve-3d;
      transform: translateZ(50px);
    }
    h2 {
      font-size: 1.75rem;
      margin-bottom: 3rem;
      font-weight: 600;
      color: #FFD700;
      text-shadow: 0 0 8px rgba(255,215,0,0.3);
      animation: slideUp 1s ease-out;
      transform-style: preserve-3d;
      transform: translateZ(30px);
    }
    .shop-now-btn {
      background-color: transparent;
      color: #00aaff;
      border: 2px solid #00aaff;
      padding: 1rem 3rem;
      font-size: 1.5rem;
      font-weight: 700;
      border-radius: 50px;
      cursor: pointer;
      transition: all 0.4s ease;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      box-shadow: 0 0 20px rgba(0,170,255,0.3);
      position: relative;
      overflow: hidden;
      animation: pulse 2s infinite;
      transform-style: preserve-3d;
      transform: translateZ(20px);
    }
    .shop-now-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(
        90deg,
        transparent,
        rgba(0,170,255,0.2),
        transparent
      );
      transition: 0.5s;
    }
    .shop-now-btn:hover::before {
      left: 100%;
    }
    .shop-now-btn:hover {
      background-color: #00aaff;
      color: #000;
      box-shadow: 0 0 30px rgba(0,170,255,0.6);
      transform: translateZ(30px) scale(1.1);
    }
    .shop-now-btn svg {
      width: 1.5em;
      height: 1.5em;
      vertical-align: middle;
      transition: transform 0.3s ease;
    }
    .shop-now-btn:hover svg {
      transform: scale(1.2) rotateY(180deg);
    }
    .logo-img {
      display: block;
      margin: 0 auto 30px auto;
      max-width: 320px;
      height: auto;
      padding: 10px;
      border: 3px solid #fff;
      border-radius: 35px;
      box-sizing: border-box;
      position: relative;
      overflow: hidden;
      animation: logoFloat 3s ease-in-out infinite;
      filter: drop-shadow(0 0 15px rgba(0,170,255,0.5));
      transform-style: preserve-3d;
      transform: translateZ(40px);
    }
    @keyframes logoFloat {
      0%, 100% {
        box-shadow: 0 0 20px rgba(0,170,255,0.5);
        transform: translateZ(40px) rotateX(0deg) rotateY(0deg);
      }
      25% {
        box-shadow: 0 0 35px 15px rgba(0,170,255,0.7);
        transform: translateZ(60px) rotateX(5deg) rotateY(5deg);
      }
      50% {
        box-shadow: 0 0 35px 15px rgba(0,170,255,0.7);
        transform: translateZ(60px) rotateX(-5deg) rotateY(-5deg);
      }
      75% {
        box-shadow: 0 0 35px 15px rgba(0,170,255,0.7);
        transform: translateZ(60px) rotateX(5deg) rotateY(-5deg);
      }
    }
    @keyframes floatIn {
      0% {
        opacity: 0;
        transform: translateZ(-100px) rotateX(20deg);
      }
      100% {
        opacity: 1;
        transform: translateZ(0) rotateX(0);
      }
    }
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-50px) translateZ(0);
      }
      to {
        opacity: 1;
        transform: translateY(0) translateZ(50px);
      }
    }
    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(50px) translateZ(0);
      }
      to {
        opacity: 1;
        transform: translateY(0) translateZ(30px);
      }
    }
    @keyframes pulse {
      0% { transform: translateZ(20px) scale(1); }
      50% { transform: translateZ(30px) scale(1.05); }
      100% { transform: translateZ(20px) scale(1); }
    }
    footer {
      margin-top: 2rem;
      color: #aaa;
      font-size: 1rem;
      position: relative;
      z-index: 2;
      animation: fadeIn 2s ease-out;
      transform-style: preserve-3d;
      transform: translateZ(10px);
    }
    /* Large tablets and small desktops */
    @media (max-width: 1200px) {
      h1 { font-size: 3.5rem; }
      h2 { font-size: 1.5rem; }
      .logo-img { max-width: 280px; }
      .shop-now-btn { 
        font-size: 1.3rem; 
        padding: 0.9rem 2.5rem;
      }
    }
    
    /* Tablets */
    @media (max-width: 900px) {
      h1 { font-size: 3rem; }
      h2 { font-size: 1.3rem; }
      .logo-img { max-width: 240px; }
      .shop-now-btn { 
        font-size: 1.2rem; 
        padding: 0.8rem 2rem;
      }
      
      .carousel {
        height: 70vh;
        height: 70dvh;
      }
      .slides {
        height: 70vh;
        height: 70dvh;
      }
      .slide {
        height: 70vh;
        height: 70dvh;
      }
      .slide video {
        height: 70vh;
        height: 70dvh;
      }
      .video-content h1 {
        font-size: 2.5rem;
      }
      .video-content h2 {
        font-size: 1.2rem;
      }
      .nav-button {
        font-size: 1.8rem;
        padding: 0.4rem 0.8rem;
      }
      
      /* Adjust content positioning for slides 2 and 4 */
      .slide:nth-child(2) .video-content,
      .slide:nth-child(4) .video-content {
        padding-top: 8rem;
      }
    }
    
    /* Large mobile devices */
    @media (max-width: 768px) {
      h1 { font-size: 2.5rem; }
      h2 { font-size: 1.2rem; }
      .logo-img { max-width: 200px; }
      .shop-now-btn { 
        font-size: 1.1rem; 
        padding: 0.8rem 1.8rem;
      }
      
      .carousel {
        height: 65vh;
        height: 65dvh;
      }
      .slides {
        height: 65vh;
        height: 65dvh;
      }
      .slide {
        height: 65vh;
        height: 65dvh;
      }
      .slide video {
        height: 65vh;
        height: 65dvh;
      }
      .video-content h1 {
        font-size: 2.2rem;
      }
      .video-content h2 {
        font-size: 1.1rem;
      }
      .nav-button {
        font-size: 1.6rem;
        padding: 0.3rem 0.7rem;
      }
      
      /* Adjust content positioning for slides 2 and 4 */
      .slide:nth-child(2) .video-content,
      .slide:nth-child(4) .video-content {
        padding-top: 6rem;
      }
    }
    
    /* Medium mobile devices */
    @media (max-width: 600px) {
      h1 { font-size: 2.2rem; }
      h2 { font-size: 1.1rem; }
      .logo-img { max-width: 180px; }
      .shop-now-btn { 
        font-size: 1rem; 
        padding: 0.7rem 1.5rem;
      }
      
      .carousel {
        height: 60vh;
        height: 60dvh;
      }
      .slides {
        height: 60vh;
        height: 60dvh;
      }
      .slide {
        height: 60vh;
        height: 60dvh;
      }
      .slide video {
        height: 60vh;
        height: 60dvh;
      }
      .video-content h1 {
        font-size: 2rem;
      }
      .video-content h2 {
        font-size: 1rem;
      }
      .nav-button {
        font-size: 1.5rem;
        padding: 0.3rem 0.6rem;
      }
      
      /* Adjust content positioning for slides 2 and 4 */
      .slide:nth-child(2) .video-content,
      .slide:nth-child(4) .video-content {
        padding-top: 5rem;
      }
    }
    
    /* Small mobile devices */
    @media (max-width: 480px) {
      h1 { font-size: 1.8rem; }
      h2 { font-size: 1rem; }
      .logo-img { max-width: 150px; }
      .shop-now-btn { 
        font-size: 0.9rem; 
        padding: 0.6rem 1.3rem;
        bottom: 60px; /* Move button higher above bottom edge */
      }
      
      .carousel {
        height: 55vh;
        height: 55dvh;
      }
      .slides {
        height: 55vh;
        height: 55dvh;
      }
      .slide {
        height: 55vh;
        height: 55dvh;
      }
      .slide video {
        height: 55vh;
        height: 55dvh;
      }
      .video-content h1 {
        font-size: 1.6rem;
      }
      .video-content h2 {
        font-size: 0.9rem;
      }
      .nav-button {
        font-size: 1.3rem;
        padding: 0.2rem 0.5rem;
      }
      
      /* Adjust content positioning for slides 2 and 4 */
      .slide:nth-child(2) .video-content,
      .slide:nth-child(4) .video-content {
        padding-top: 4rem;
      }
    }
    
    /* Extra small mobile devices */
    @media (max-width: 360px) {
      h1 { font-size: 1.5rem; }
      h2 { font-size: 0.9rem; }
      .logo-img { max-width: 120px; }
      .shop-now-btn { 
        font-size: 0.8rem; 
        padding: 0.5rem 1.1rem;
        bottom: 50px; /* Move button higher above bottom edge */
      }
      
      .carousel {
        height: 50vh;
        height: 50dvh;
      }
      .slides {
        height: 50vh;
        height: 50dvh;
      }
      .slide {
        height: 50vh;
        height: 50dvh;
      }
      .slide video {
        height: 50vh;
        height: 50dvh;
      }
      .video-content h1 {
        font-size: 1.4rem;
      }
      .video-content h2 {
        font-size: 0.8rem;
      }
      .nav-button {
        font-size: 1.1rem;
        padding: 0.2rem 0.4rem;
      }
      
      /* Adjust content positioning for slides 2 and 4 */
      .slide:nth-child(2) .video-content,
      .slide:nth-child(4) .video-content {
        padding-top: 3rem;
      }
    }
    
    /* Landscape orientation adjustments for mobile */
    @media (max-height: 500px) and (orientation: landscape) {
      .carousel {
        height: 100vh;
        height: 100dvh;
      }
      .slides {
        height: 100vh;
        height: 100dvh;
      }
      .slide {
        height: 100vh;
        height: 100dvh;
      }
      .slide video {
        height: 100vh;
        height: 100dvh;
      }
      
      .video-content h1 {
        font-size: 1.8rem;
        margin-bottom: 0.3rem;
      }
      .video-content h2 {
        font-size: 1rem;
        margin-bottom: 1.5rem;
      }
      
      .logo-img {
        max-width: 100px;
        margin-bottom: 15px;
      }
      
      /* Adjust content positioning for slides 2 and 4 */
      .slide:nth-child(2) .video-content,
      .slide:nth-child(4) .video-content {
        padding-top: 2rem;
      }
    }

    /* iPhone notch and safe area support */
    @supports (padding: max(0px)) {
      body {
        padding-left: max(0px, env(safe-area-inset-left));
        padding-right: max(0px, env(safe-area-inset-right));
        padding-top: max(0px, env(safe-area-inset-top));
        padding-bottom: max(0px, env(safe-area-inset-bottom));
      }
    }

    /* Touch device optimizations */
    @media (hover: none) and (pointer: coarse) {
      .nav-button {
        min-width: 44px;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      
      .shop-now-btn {
        min-height: 44px;
        touch-action: manipulation;
      }

      /* Improve touch targets for mobile */
      .indicator__item {
        min-width: 44px;
        min-height: 44px;
        margin: 0 4px;
      }

      /* Optimize video content for mobile */
      .video-content {
        padding: 0 1.5rem;
      }

      .video-content h1 {
        line-height: 1.2;
        margin-bottom: 0.8rem;
      }

      .video-content h2 {
        line-height: 1.3;
        margin-bottom: 2rem;
      }
    }

    /* High DPI display optimizations */
    @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
      .logo-img {
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
      }
    }

    /* Reduce motion for users who prefer it */
    @media (prefers-reduced-motion: reduce) {
      .carousel,
      .slides,
      .video-content,
      .nav-button,
      .shop-now-btn,
      .logo-img {
        animation: none !important;
        transition: none !important;
      }
      
      .slides {
        transition: transform 0.1s ease-in-out !important;
      }
    }
  </style>
  <style>
    /* Carousel container */
    .carousel {
      position: relative;
      width: 100vw;
      width: 100%;
      height: 100vh; /* Changed from calc(100vh - 60px) to 100vh */
      height: 100dvh; /* Dynamic viewport height for mobile */
      overflow: hidden;
      /* Prevent horizontal scroll on mobile */
      max-width: 100vw;
    }
    /* Slides container */
    .slides {
      display: flex;
      width: 400vw; /* 4 slides */
      width: 400%;
      height: 100vh; /* Changed from calc(100vh - 60px) to 100vh */
      height: 100dvh; /* Dynamic viewport height for mobile */
      transition: transform 0.5s ease-in-out;
      /* Optimize for mobile performance */
      will-change: transform;
      -webkit-transform-style: preserve-3d;
      transform-style: preserve-3d;
    }
    /* Each slide */
    .slide {
      position: relative;
      width: 100vw;
      width: 25%; /* 100% / 4 slides */
      height: 100vh; /* Changed from calc(100vh - 60px) to 100vh */
      height: 100dvh; /* Dynamic viewport height for mobile */
      flex-shrink: 0;
      overflow: hidden;
    }
    /* Video inside slide */
    .slide video {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
      z-index: 0;
      pointer-events: none;
      /* Optimize video performance on mobile */
      -webkit-transform: translateZ(0);
      transform: translateZ(0);
      /* Prevent video from causing layout shifts */
      backface-visibility: hidden;
      -webkit-backface-visibility: hidden;
      /* Optimize for mobile playback */
      playsinline: true;
      preload: metadata;
    }
    /* Content overlay */
    .video-content {
      position: relative;
      z-index: 1;
      color: #fff;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      height: 100%;
      text-align: center;
      padding: 0 1rem;
      background: rgba(0,0,0,0.4); /* Slightly darker for better text readability */
      opacity: 0;
      transition: opacity 1s ease-in-out;
      /* Mobile optimizations */
      -webkit-transform: translateZ(0);
      transform: translateZ(0);
      /* Ensure text is readable on all devices */
      text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
      /* Prevent text selection on mobile */
      -webkit-touch-callout: none;
      -webkit-user-select: none;
      -khtml-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
      user-select: none;
    }
     /* Move video content a little lower for second slide */
     .slide:nth-child(2) .video-content {
       justify-content: center;
       padding-top: 12rem;
     }
     /* Align video content of fourth slide with second slide */
     .slide:nth-child(4) .video-content {
       justify-content: center;
       padding-top: 12rem;
     }
     
     /* Large tablets and small desktops */
     @media (max-width: 1200px) {
       .slide:nth-child(2) .video-content,
       .slide:nth-child(4) .video-content {
         padding-top: 10rem;
       }
     }
     
     /* Tablets */
     @media (max-width: 900px) {
       .slide:nth-child(2) .video-content,
       .slide:nth-child(4) .video-content {
         padding-top: 8rem;
       }
     }
     
     /* Large mobile devices */
     @media (max-width: 768px) {
       .slide:nth-child(2) .video-content,
       .slide:nth-child(4) .video-content {
         padding-top: 6rem;
       }
     }
     
     /* Medium mobile devices */
     @media (max-width: 600px) {
       .slide:nth-child(2) .video-content,
       .slide:nth-child(4) .video-content {
         padding-top: 5rem;
       }
     }
     
     /* Small mobile devices */
     @media (max-width: 480px) {
       .slide:nth-child(2) .video-content,
       .slide:nth-child(4) .video-content {
         padding-top: 4rem;
       }
     }
     
     /* Extra small mobile devices */
     @media (max-width: 360px) {
       .slide:nth-child(2) .video-content,
       .slide:nth-child(4) .video-content {
         padding-top: 3rem;
       }
     }
     
     /* Landscape orientation adjustments */
     @media (max-height: 500px) and (orientation: landscape) {
       .slide:nth-child(2) .video-content,
       .slide:nth-child(4) .video-content {
         padding-top: 2rem;
       }
     }
     .video-content.visible {
       opacity: 1;
     }
    /* Navigation buttons */
    .nav-button {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background-color: rgba(0,0,0,0.3); /* Semi-transparent background for better visibility */
      border: none;
      color: #fff;
      font-size: 2rem;
      padding: 0.5rem 1rem;
      cursor: pointer;
      z-index: 2;
      user-select: none;
      opacity: 0;
      transition: all 0.3s ease;
      pointer-events: none;
      /* Mobile touch optimizations */
      min-width: 44px;
      min-height: 44px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      /* Prevent text selection on mobile */
      -webkit-touch-callout: none;
      -webkit-user-select: none;
      -khtml-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
      user-select: none;
      touch-action: manipulation;
    }
    .nav-button.visible {
      opacity: 1;
      pointer-events: auto;
    }
    .nav-button:hover,
    .nav-button:active {
      background-color: rgba(0,0,0,0.5);
      transform: translateY(-50%) scale(1.1);
    }
    .nav-button:focus {
      outline: 2px solid #00aaff;
      outline-offset: 2px;
    }
    .nav-prev {
      left: 10px;
      border-radius: 50%;
    }
    .nav-next {
      right: 10px;
      border-radius: 50%;
    }
    
    /* Large tablets and small desktops */
    @media (max-width: 1200px) {
      .nav-button {
        font-size: 1.8rem;
        padding: 0.4rem 0.8rem;
      }
      .nav-prev { left: 8px; }
      .nav-next { right: 8px; }
    }
    
    /* Tablets */
    @media (max-width: 900px) {
      .nav-button {
        font-size: 1.6rem;
        padding: 0.3rem 0.7rem;
      }
      .nav-prev { left: 6px; }
      .nav-next { right: 6px; }
    }
    
    /* Large mobile devices */
    @media (max-width: 768px) {
      .nav-button {
        font-size: 1.4rem;
        padding: 0.3rem 0.6rem;
      }
      .nav-prev { left: 5px; }
      .nav-next { right: 5px; }
    }
    
    /* Medium mobile devices */
    @media (max-width: 600px) {
      .nav-button {
        font-size: 1.2rem;
        padding: 0.2rem 0.5rem;
      }
      .nav-prev { left: 4px; }
      .nav-next { right: 4px; }
    }
    
    /* Small mobile devices */
    @media (max-width: 480px) {
      .nav-button {
        font-size: 1rem;
        padding: 0.2rem 0.4rem;
      }
      .nav-prev { left: 3px; }
      .nav-next { right: 3px; }
    }
    
    /* Extra small mobile devices */
    @media (max-width: 360px) {
      .nav-button {
        font-size: 0.9rem;
        padding: 0.1rem 0.3rem;
      }
      .nav-prev { left: 2px; }
      .nav-next { right: 2px; }
    }
    
    /* Landscape orientation adjustments */
    @media (max-height: 500px) and (orientation: landscape) {
      .nav-button {
        font-size: 1.1rem;
        padding: 0.2rem 0.4rem;
      }
      .nav-prev { left: 3px; }
      .nav-next { right: 3px; }
    }

    /* Carousel indicators container */
    #carouselIndicators {
      position: absolute;
      bottom: 15px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 12px;
      z-index: 3;
    }
    
    /* Large tablets and small desktops */
    @media (max-width: 1200px) {
      #carouselIndicators {
        bottom: 20px;
        gap: 10px;
      }
      .indicator__item {
        width: 40px;
        height: 8px;
      }
      .indicator__label-line {
        width: 36px;
        height: 3px;
      }
    }
    
    /* Tablets */
    @media (max-width: 900px) {
      #carouselIndicators {
        bottom: 18px;
        gap: 8px;
      }
      .indicator__item {
        width: 36px;
        height: 7px;
      }
      .indicator__label-line {
        width: 32px;
        height: 3px;
      }
    }
    
    /* Large mobile devices */
    @media (max-width: 768px) {
      #carouselIndicators {
        bottom: 16px;
        gap: 6px;
      }
      .indicator__item {
        width: 32px;
        height: 6px;
      }
      .indicator__label-line {
        width: 28px;
        height: 2px;
      }
    }
    
    /* Medium mobile devices */
    @media (max-width: 600px) {
      #carouselIndicators {
        bottom: 14px;
        gap: 5px;
      }
      .indicator__item {
        width: 28px;
        height: 5px;
      }
      .indicator__label-line {
        width: 24px;
        height: 2px;
      }
    }
    
    /* Small mobile devices */
    @media (max-width: 480px) {
      #carouselIndicators {
        bottom: 12px;
        gap: 4px;
      }
      .indicator__item {
        width: 24px;
        height: 4px;
      }
      .indicator__label-line {
        width: 20px;
        height: 2px;
      }
    }
    
    /* Extra small mobile devices */
    @media (max-width: 360px) {
      #carouselIndicators {
        bottom: 10px;
        gap: 3px;
      }
      .indicator__item {
        width: 20px;
        height: 3px;
      }
      .indicator__label-line {
        width: 16px;
        height: 2px;
      }
    }
    
    /* Landscape orientation adjustments */
    @media (max-height: 500px) and (orientation: landscape) {
      #carouselIndicators {
        bottom: 8px;
        gap: 4px;
      }
      .indicator__item {
        width: 22px;
        height: 3px;
      }
      .indicator__label-line {
        width: 18px;
        height: 2px;
      }
    }

    /* Indicator buttons styled as circles */
    .indicator__item {
      width: 14px;
      height: 14px;
      border-radius: 50%;
      background-color: rgba(255, 255, 255, 0.5);
      border: none;
      cursor: pointer;
      padding: 0;
      transition: background-color 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* Active indicator */
    .indicator__item--active {
      background-color: #00aaff;
      box-shadow: 0 0 8px #00aaff;
    }

    /* Optional: hover effect for indicators */
    /* Removed hover effect for line indicators */
    /* .indicator__item:not(.indicator__item--active):hover {
      background-color: rgba(255, 255, 255, 0.8);
    } */

    /* Hide label wraps to show only circles */
    .indicator__label-wrap {
      display: none;
    }
  </style>
  <style>
    .title-bar {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 60px;
      background-color: rgba(0, 0, 0, 0.7);
      display: flex;
      align-items: center;
      padding: 0 20px;
      z-index: 1000;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
    }
    .title-bar-logo {
      height: 40px;
      width: auto;
      user-select: none;
    }
    /* Adjust body padding to avoid content hidden behind fixed header */
    body {
      padding-top: 60px !important;
    }
  </style>
  <style>
    /* Added title bar styles for logo in title bar */
    .title-bar {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 60px;
      background-color: rgba(0, 0, 0, 0.7);
      display: flex;
      align-items: center;
      padding: 0 20px;
      z-index: 1000;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
    }
    .title-bar-logo {
      height: 40px;
      width: auto;
      user-select: none;
    }
    /* Adjust body padding to avoid content hidden behind fixed header */
    body {
      padding-top: 60px !important;
    }
  </style>
</head>
  <body>
    <div class="carousel">
      <div class="slides" id="slides">
        <div class="slide">
          <video autoplay muted loop playsinline preload="metadata" webkit-playsinline>
            <source src="images/iPhone 16.mp4" type="video/mp4" />
            Your browser does not support the video tag.
          </video>
          <div class="video-content">
            <img src="images/iCenter.png" alt="BISLIG iCENTER Logo" class="logo-img" />
            <h1>Welcome to BiSLIG iCENTER</h1>
            <h2>The No.#1 Supplier of iPhones in Mindanao</h2>
          </div>
        </div>
        <div class="slide">
          <video autoplay muted loop playsinline preload="metadata" webkit-playsinline>
            <source src="images/iPad Air M2.mp4" type="video/mp4" />
            Your browser does not support the video tag.
          </video>
          <div class="video-content">
            <h1>Introducing iPad Air M2</h1>
            <h2>Experience power and portability</h2>
          </div>
        </div>
        <div class="slide">
          <video autoplay muted loop playsinline preload="metadata" webkit-playsinline>
            <source src="images/MacBook.mp4" type="video/mp4" />
            Your browser does not support the video tag.
          </video>
          <div class="video-content">
            <h1>Discover MacBook</h1>
            <h2>Power and performance redefined</h2>
          </div>
        </div>
        <div class="slide">
          <video autoplay muted loop playsinline preload="metadata" webkit-playsinline>
            <source src="images/AirPods.mp4" type="video/mp4" />
            Your browser does not support the video tag.
          </video>
          <div class="video-content">
            <h1>Introducing AirPods</h1>
            <h2>Immersive sound experience</h2>
          </div>
        </div>
      </div>
      <button class="nav-button nav-prev" id="prevBtn">&#10094;</button>
      <button class="nav-button nav-next" id="nextBtn">&#10095;</button>
    </div>
    <div class="carousel-indicators" id="carouselIndicators">
      <!-- Indicators will be dynamically added here -->
    </div>
    <a href="kiosk.php" class="shop-now-bottom-btn">
      <span>Shop Now</span>
    </a>
    <!-- Add this toggle button just above the closing </body> tag, after your carousels and before scripts -->
<button id="toggle-sound-btn" class="toggle-sound-btn" aria-pressed="false">🔇</button>

<script>
    const slides = document.getElementById('slides');
    const totalSlides = slides.children.length;
    let currentIndex = 0;
    let isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
    let touchStartX = 0;
    let touchEndX = 0;
    let isTransitioning = false;

    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const indicatorsContainer = document.getElementById('carouselIndicators');

    // Video durations in seconds for each slide
    const videoDurations = [28, 43, 45, 46];

    // Product names for each indicator
    const productNames = ["iPhone", "iPad Air", "MacBook", "AirPods"];

    // Mobile video optimization
    function optimizeVideosForMobile() {
      const videos = document.querySelectorAll('video');
      videos.forEach(video => {
        // Set video attributes for better mobile performance
        video.setAttribute('playsinline', 'true');
        video.setAttribute('muted', 'true');
        video.setAttribute('preload', 'metadata');
        
        // For mobile devices, use lower quality settings
        if (isTouchDevice) {
          video.setAttribute('poster', ''); // Remove poster for better performance
        }
        
        // Handle video loading errors
        video.addEventListener('error', function() {
          console.warn('Video failed to load:', video.src);
        });
        
        // Optimize video playback
        video.addEventListener('loadedmetadata', function() {
          // Video is ready to play
        });
      });
    }

    // Create indicators dynamically with structure similar to example
    for (let i = 0; i < totalSlides; i++) {
      const button = document.createElement('button');
      button.classList.add('indicator__item');
      if (i === 0) button.classList.add('indicator__item--active', 'indicator__item--not-hover');
      button.setAttribute('role', 'tab');
      button.setAttribute('data-indicator-delay', videoDurations[i] * 1000);
      button.setAttribute('aria-controls', `slide-${i}`);
      button.setAttribute('aria-selected', i === 0 ? 'true' : 'false');
      button.setAttribute('tabindex', i === 0 ? '0' : '-1');

      // --- Product label above the line ---
      const productLabel = document.createElement('span');
      productLabel.classList.add('indicator__product-label');
      productLabel.textContent = productNames[i];

      // Only create the line indicator
      const labelWrap = document.createElement('span');
      labelWrap.classList.add('indicator__label-wrap');

      const labelLine = document.createElement('span');
      labelLine.classList.add('indicator__label-line');

      const labelLineFilled = document.createElement('span');
      labelLineFilled.classList.add('indicator__label-line-filled');
      labelLineFilled.style.transform = 'scaleX(0)'; // initial scale 0

      labelLine.appendChild(labelLineFilled);

      const label = document.createElement('span');
      label.classList.add('indicator__label');
      label.textContent = `Slide ${i + 1} (${videoDurations[i]}s)`;

      labelWrap.appendChild(productLabel); // Add product label above the line
      labelWrap.appendChild(labelLine);
      labelWrap.appendChild(label);

      button.appendChild(labelWrap);

      // Make all indicators visible (show the line for all, not just active)
      labelWrap.style.display = 'block';

      button.addEventListener('click', () => {
        currentIndex = i;
        updateSlidePosition(true);
      });

      indicatorsContainer.appendChild(button);
    }

    // Add this global timer variable
    let indicatorTimer = null;

    /**
     * Update the active state of indicators and animate label lines
     */
    function updateIndicators(reset = false) {
      const indicators = indicatorsContainer.children;
      // Clear any previous timer
      if (indicatorTimer) {
        clearTimeout(indicatorTimer);
        indicatorTimer = null;
      }
      for (let i = 0; i < indicators.length; i++) {
        const labelLineFilled = indicators[i].querySelector('.indicator__label-line-filled');
        // Show all label lines
        const labelWrap = indicators[i].querySelector('.indicator__label-wrap');
        if (labelWrap) labelWrap.style.display = 'block';
        if (i === currentIndex) {
          indicators[i].classList.add('indicator__item--active');
          indicators[i].classList.remove('indicator__item--not-hover');
          // Animate label line filled scaleX to 1
          if (labelLineFilled) {
            labelLineFilled.style.transition = reset ? 'none' : `transform ${videoDurations[i]}s linear`;
            labelLineFilled.style.transform = reset ? 'scaleX(0)' : 'scaleX(1)';
            // Force reflow if reset, then animate
            if (reset) {
              void labelLineFilled.offsetWidth;
              setTimeout(() => {
                labelLineFilled.style.transition = `transform ${videoDurations[i]}s linear`;
                labelLineFilled.style.transform = 'scaleX(1)';
              }, 20);
            }
          }
          indicators[i].setAttribute('aria-selected', 'true');
          indicators[i].setAttribute('tabindex', '0');
          // Start timer for auto-slide
          indicatorTimer = setTimeout(() => {
            currentIndex = (currentIndex + 1) % totalSlides;
            updateSlidePosition(true);
          }, videoDurations[i] * 1000);
        } else {
          indicators[i].classList.remove('indicator__item--active');
          indicators[i].classList.add('indicator__item--not-hover');
          // Animate label line filled scaleX to 0
          if (labelLineFilled) {
            labelLineFilled.style.transition = 'transform 0.3s ease';
            labelLineFilled.style.transform = 'scaleX(0)';
          }
          indicators[i].setAttribute('aria-selected', 'false');
          indicators[i].setAttribute('tabindex', '-1');
        }
      }
    }

    /**
     * Update slide position and video playback based on currentIndex
     * @param {boolean} resetIndicator - If true, reset indicator animation
     */
    let videoContentTimers = new Array(totalSlides).fill(null);
    function updateSlidePosition(resetIndicator = false) {
      slides.style.transform = 'translateX(' + (-currentIndex * 100) + 'vw)';
      updateIndicators(resetIndicator);

      // Show all video-content initially for current slide
      for (let i = 0; i < totalSlides; i++) {
        const videoContent = slides.children[i].querySelector('.video-content');
        if (videoContent) {
          if (i === currentIndex) {
            videoContent.classList.add('visible');
          } else {
            videoContent.classList.remove('visible');
          }
        }
      }

      // Clear previous timer for all slides
      for (let i = 0; i < totalSlides; i++) {
        if (videoContentTimers[i]) {
          clearTimeout(videoContentTimers[i]);
          videoContentTimers[i] = null;
        }
      }

      // Fade-out durations in milliseconds for each slide
      const fadeOutDurations = [6000, 1000, 3800, 1000];

      // Hide video-content of current slide after its specific duration
      videoContentTimers[currentIndex] = setTimeout(() => {
        const currentVideoContent = slides.children[currentIndex].querySelector('.video-content');
        if (currentVideoContent) {
          currentVideoContent.classList.remove('visible');
        }
      }, fadeOutDurations[currentIndex]);

      // Pause all videos except current
      for (let i = 0; i < totalSlides; i++) {
        const video = slides.children[i].querySelector('video');
        if (i === currentIndex) {
          video.currentTime = 0;
          video.play();
          // Remove any previous event listeners to avoid duplicates
          video.onended = null;
          // Pause video at the end, but auto-slide is handled by indicator timer
          video.onended = () => {
            // Do nothing, auto-slide is handled by indicator timer
          };
        } else {
          video.pause();
          video.currentTime = 0;
          video.onended = null;
        }
      }
    }

    prevBtn.addEventListener('click', () => {
      currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
      updateSlidePosition(true);
    });

    nextBtn.addEventListener('click', () => {
      currentIndex = (currentIndex + 1) % totalSlides;
      updateSlidePosition(true);
    });

    // Touch gesture support for mobile
    function addTouchSupport() {
      const carousel = document.querySelector('.carousel');
      
      carousel.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
      }, { passive: true });
      
      carousel.addEventListener('touchend', function(e) {
        if (isTransitioning) return;
        
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
      }, { passive: true });
      
      function handleSwipe() {
        const swipeThreshold = 50; // Minimum distance for swipe
        const diff = touchStartX - touchEndX;
        
        if (Math.abs(diff) > swipeThreshold) {
          isTransitioning = true;
          
          if (diff > 0) {
            // Swipe left - next slide
            currentIndex = (currentIndex + 1) % totalSlides;
          } else {
            // Swipe right - previous slide
            currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
          }
          
          updateSlidePosition(true);
          
          // Reset transition flag after animation
          setTimeout(() => {
            isTransitioning = false;
          }, 500);
        }
      }
    }

    // Prevent zoom on double tap for mobile
    function preventZoom() {
      let lastTouchEnd = 0;
      document.addEventListener('touchend', function(event) {
        const now = (new Date()).getTime();
        if (now - lastTouchEnd <= 300) {
          event.preventDefault();
        }
        lastTouchEnd = now;
      }, false);
    }

    // Initialize
    updateSlidePosition(true);
    optimizeVideosForMobile();
    addTouchSupport();
    preventZoom();
  </script>
  <script>
    (function() {
      const prevBtn = document.getElementById('prevBtn');
      const nextBtn = document.getElementById('nextBtn');
      let hideTimeout;
      let isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

      function showButtons() {
        prevBtn.classList.add('visible');
        nextBtn.classList.add('visible');
        clearTimeout(hideTimeout);
        hideTimeout = setTimeout(() => {
          prevBtn.classList.remove('visible');
          nextBtn.classList.remove('visible');
        }, 3000); // Show longer on mobile
      }

      function handleMouseMove(e) {
        const threshold = 100; // px from edge to show buttons
        if (e.clientX < threshold) {
          prevBtn.classList.add('visible');
        } else {
          prevBtn.classList.remove('visible');
        }
        if (window.innerWidth - e.clientX < threshold) {
          nextBtn.classList.add('visible');
        } else {
          nextBtn.classList.remove('visible');
        }
        clearTimeout(hideTimeout);
        hideTimeout = setTimeout(() => {
          prevBtn.classList.remove('visible');
          nextBtn.classList.remove('visible');
        }, 2000);
      }

      function handleTouchMove(e) {
        if (!e.touches || e.touches.length === 0) return;
        const touch = e.touches[0];
        const threshold = 100;
        if (touch.clientX < threshold) {
          prevBtn.classList.add('visible');
        } else {
          prevBtn.classList.remove('visible');
        }
        if (window.innerWidth - touch.clientX < threshold) {
          nextBtn.classList.add('visible');
        } else {
          nextBtn.classList.remove('visible');
        }
        clearTimeout(hideTimeout);
        hideTimeout = setTimeout(() => {
          prevBtn.classList.remove('visible');
          nextBtn.classList.remove('visible');
        }, 3000); // Show longer on mobile
      }

      function handleTouchStart() {
        // Show buttons immediately on touch for mobile
        if (isTouchDevice) {
          showButtons();
        }
      }

      // Add touch start listener for immediate button visibility on mobile
      document.addEventListener('touchstart', handleTouchStart, { passive: true });
      document.addEventListener('mousemove', handleMouseMove);
      document.addEventListener('touchmove', handleTouchMove, { passive: true });
      
      // Show buttons initially for mobile devices
      if (isTouchDevice) {
        showButtons();
      } else {
        // Initially hide buttons after 2 seconds for desktop
        hideTimeout = setTimeout(() => {
          prevBtn.classList.remove('visible');
          nextBtn.classList.remove('visible');
        }, 2000);
      }
    })();
  </script>
  <style>
    header {
      display: none; /* Hide header if present */
    }
    .header-logo {
      display: none;
    }
    body {
      padding-top: 0 !important; /* Remove top padding */
    }
    @media (max-width: 600px) {
      header {
        display: none;
      }
      .header-logo {
        display: none;
      }
      body {
        padding-top: 0 !important;
      }
    }
  </style>
  <style>
    /* Remove dot indicators, only show the line */
    .indicator__dot-wrap,
    .indicator__dot,
    .indicator__dot-inner {
      display: none !important;
    }
    .indicator__item {
      width: 44px;
      height: 10px;
      background: transparent;
      border: none;
      cursor: pointer;
      padding: 0;
      margin: 0 2px;
      display: flex;
      flex-direction: column; /* Align children vertically */
      align-items: center;
      justify-content: center;
      box-shadow: none;
    }
    .indicator__label-line {
      display: block;
      width: 40px;
      height: 4px;
      background: rgba(255,255,255,0.2);
      border-radius: 2px;
      overflow: hidden;
      margin: 0 auto 2px auto;
      position: relative;
    }
    .indicator__label-line-filled {
      display: block;
      height: 100%;
      width: 100%;
      background: #00aaff;
      border-radius: 2px;
      transform: scaleX(0);
      transform-origin: left;
      transition: transform 0.3s ease;
      position: absolute;
      left: 0;
      top: 0;
    }
    .indicator__item--active .indicator__label-line-filled {
      /* transition handled in JS */
    }
    /* Show all indicator lines */
    .indicator__label-wrap {
      display: block !important;
    }
    .indicator__label {
      display: none;
    }
    #carouselIndicators {
      position: absolute;
      bottom: 15px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 12px;
      z-index: 3;
    }
    .indicator__product-label {
      display: none; /* Hide by default */
      font-size: 0.75rem;
      color: #fff;
      opacity: 0.85;
      margin-bottom: 2px;
      letter-spacing: 0.5px;
      font-weight: 500;
      text-align: center;
      line-height: 1.1;
      white-space: nowrap;
      text-shadow: 0 1px 2px #003344;
    }
    .indicator__item--active:hover .indicator__product-label {
      display: block; /* Show only if active and hovered */
    }
  </style>
  <style>
    .shop-now-bottom-btn {
      position: fixed;
      left: 50%;
      bottom: 50px;
      transform: translateX(-50%);
      background: rgba(255,255,255,0.15); /* More transparent */
      color:rgb(0, 0, 0);
      border: none;
      border-radius: 32px;
      padding: 1rem 2.5rem;
      font-size: 1.25rem;
      font-weight: 700;
      letter-spacing: 1px;
      box-shadow: 0 4px 24px rgba(0,0,0,0.10);
      display: flex;
      align-items: center;
      gap: 0.7rem;
      text-decoration: none;
      z-index: 100;
      transition: background 0.2s, color 0.2s, box-shadow 0.2s, transform 0.15s;
      cursor: pointer;
      backdrop-filter: blur(16px); /* Stronger blur */
      -webkit-backdrop-filter: blur(16px);
    }
    .shop-now-bottom-btn:hover, .shop-now-bottom-btn:focus {
      background: rgba(255,255,255,0.6);
      color:rgb(0, 0, 0);
      box-shadow: 0 8px 32px rgba(0,170,255,0.10);
      transform: translateX(-50%) scale(1.04);
      outline: none;
    }
    /* Large tablets and small desktops */
    @media (max-width: 1200px) {
      .shop-now-bottom-btn {
        font-size: 1.2rem;
        padding: 0.9rem 2.2rem;
        bottom: 40px;
      }
    }
    
    /* Tablets */
    @media (max-width: 900px) {
      .shop-now-bottom-btn {
        font-size: 1.1rem;
        padding: 0.8rem 2rem;
        bottom: 35px;
      }
    }
    
    /* Large mobile devices */
    @media (max-width: 768px) {
      .shop-now-bottom-btn {
        font-size: 1rem;
        padding: 0.8rem 1.8rem;
        bottom: 30px;
      }
    }
    
    /* Medium mobile devices */
    @media (max-width: 600px) {
      .shop-now-bottom-btn {
        font-size: 0.95rem;
        padding: 0.7rem 1.5rem;
        bottom: 70px; /* Move button higher above bottom edge */
      }
    }
    
    /* Small mobile devices */
    @media (max-width: 480px) {
      .shop-now-bottom-btn {
        font-size: 0.9rem;
        padding: 0.6rem 1.3rem;
        bottom: 60px; /* Move button higher above bottom edge */
      }
    }
    
    /* Extra small mobile devices */
    @media (max-width: 360px) {
      .shop-now-bottom-btn {
        font-size: 0.8rem;
        padding: 0.5rem 1.1rem;
        bottom: 50px; /* Move button higher above bottom edge */
      }
    }
  </style>
  <style>
.toggle-sound-btn {
  position: fixed;
  left: 96%;
  bottom: 16px;
  right: auto;
  transform: translateX(-50%);
  z-index: 200;
  background: rgba(255,255,255,0.15); /* More transparent */
  color:rgb(0, 0, 0);
  border: none;
  border-radius: 24px;
  padding: 0.7rem 1.4rem;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 2px 12px rgba(0,0,0,0.13);
  backdrop-filter: blur(8px);
  transition: background 0.2s, color 0.2s, transform 0.15s;
}
.toggle-sound-btn[aria-pressed="true"] {
  background: rgba(255,255,255,0.15);
  color: #fff;
}
.toggle-sound-btn:focus {
  outline: 2px solidrgb(0, 0, 0);
}
/* Large tablets and small desktops */
@media (max-width: 1200px) {
  .toggle-sound-btn {
    left: 95%;
    font-size: 1.2rem;
    padding: 0.8rem 1.6rem;
    bottom: 20px;
  }
}

/* Tablets */
@media (max-width: 900px) {
  .toggle-sound-btn {
    left: 94%;
    font-size: 1.1rem;
    padding: 0.7rem 1.5rem;
    bottom: 18px;
  }
}

/* Large mobile devices */
@media (max-width: 768px) {
  .toggle-sound-btn {
    left: 92%;
    font-size: 1rem;
    padding: 0.6rem 1.3rem;
    bottom: 15px;
  }
}

/* Medium mobile devices */
@media (max-width: 600px) {
  .toggle-sound-btn {
    left: 50%;
    right: auto;
    bottom: 8px;
    transform: translateX(-50%);
    font-size: 0.95rem;
    padding: 0.5rem 1rem;
  }
}

/* Small mobile devices */
@media (max-width: 480px) {
  .toggle-sound-btn {
    left: 50%;
    right: auto;
    bottom: 6px;
    transform: translateX(-50%);
    font-size: 0.9rem;
    padding: 0.4rem 0.9rem;
  }
}

/* Extra small mobile devices */
@media (max-width: 360px) {
  .toggle-sound-btn {
    left: 50%;
    right: auto;
    bottom: 5px;
    transform: translateX(-50%);
    font-size: 0.8rem;
    padding: 0.3rem 0.8rem;
  }
}

/* Landscape orientation adjustments */
@media (max-height: 500px) and (orientation: landscape) {
  .toggle-sound-btn {
    left: 50%;
    right: auto;
    bottom: 5px;
    transform: translateX(-50%);
    font-size: 0.9rem;
    padding: 0.4rem 0.9rem;
  }
}
  </style>
  <script>
// Sound toggle logic for all carousels
let soundOn = false;
const toggleBtn = document.getElementById('toggle-sound-btn');

function setAllVideosMuted(muted) {
  document.querySelectorAll('video').forEach(video => {
    video.muted = muted;
    // If turning sound on, play to ensure audio resumes
    if (!muted) {
      video.play().catch(()=>{});
    }
  });
}

toggleBtn.addEventListener('click', () => {
  soundOn = !soundOn;
  setAllVideosMuted(!soundOn);
  toggleBtn.setAttribute('aria-pressed', soundOn ? 'true' : 'false');
  toggleBtn.textContent = soundOn ? '🔊' : '🔇';
});

// Ensure all videos start muted by default
setAllVideosMuted(true);
  </script>
  </body>
</html>
