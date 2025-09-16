/**
 * WEBテーラーサイト共通アニメーションスクリプト
 * スクロール連動アニメーション、パフォーマンス最適化を含む
 */

class AnimationController {
  constructor() {
    this.observers = new Map();
    this.animatedElements = new Set();
    this.isReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    this.init();
  }

  init() {
    // DOM読み込み完了後に初期化
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.setup());
    } else {
      this.setup();
    }
  }

  setup() {
    // アニメーション機能が無効化されている場合は早期リターン
    if (this.isReduced) {
      this.showAllElements();
      return;
    }

    this.setupScrollAnimations();
    this.setupHoverEffects();
    this.setupPerformanceOptimization();
    this.setupAccessibility();
  }

  /**
   * スクロール連動アニメーションの設定
   */
  setupScrollAnimations() {
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };

    const scrollObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting && !this.animatedElements.has(entry.target)) {
          this.animateElement(entry.target);
          this.animatedElements.add(entry.target);
          // パフォーマンス向上のため、一度アニメーションしたら監視を停止
          scrollObserver.unobserve(entry.target);
        }
      });
    }, observerOptions);

    // fade-in-up クラスの要素を監視
    document.querySelectorAll('.fade-in-up').forEach(el => {
      scrollObserver.observe(el);
    });

    this.observers.set('scroll', scrollObserver);
  }

  /**
   * 要素のアニメーション実行
   */
  animateElement(element) {
    // アニメーション遅延の処理
    const delay = element.style.animationDelay || '0s';
    const delayMs = parseFloat(delay) * 1000;

    setTimeout(() => {
      element.classList.add('visible');
      
      // カスタムアニメーションイベントの発火
      element.dispatchEvent(new CustomEvent('animationStart', {
        detail: { element, delay: delayMs }
      }));
    }, delayMs);
  }

  /**
   * ホバー効果の設定
   */
  setupHoverEffects() {
    // カードホバー効果
    document.querySelectorAll('.card-hover').forEach(card => {
      card.addEventListener('mouseenter', (e) => {
        if (!this.isReduced) {
          e.target.style.transform = 'translateY(-8px)';
          e.target.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        }
      });

      card.addEventListener('mouseleave', (e) => {
        e.target.style.transform = 'translateY(0)';
      });
    });

    // ボタンホバー効果
    document.querySelectorAll('.btn-gradient, button').forEach(btn => {
      btn.addEventListener('mouseenter', (e) => {
        if (!this.isReduced) {
          e.target.style.transform = 'translateY(-2px) scale(1.02)';
          e.target.style.transition = 'all 0.2s ease';
        }
      });

      btn.addEventListener('mouseleave', (e) => {
        e.target.style.transform = 'translateY(0) scale(1)';
      });
    });
  }

  /**
   * パフォーマンス最適化
   */
  setupPerformanceOptimization() {
    // スクロールイベントの最適化（throttling）
    let scrollTimer = null;
    window.addEventListener('scroll', () => {
      if (scrollTimer) return;
      
      scrollTimer = setTimeout(() => {
        this.handleScroll();
        scrollTimer = null;
      }, 16); // 約60fps
    }, { passive: true });

    // リサイズイベントの最適化
    let resizeTimer = null;
    window.addEventListener('resize', () => {
      if (resizeTimer) return;
      
      resizeTimer = setTimeout(() => {
        this.handleResize();
        resizeTimer = null;
      }, 100);
    });
  }

  /**
   * スクロール処理
   */
  handleScroll() {
    const scrollY = window.pageYOffset;
    
    // パララックス効果（軽量版）
    document.querySelectorAll('.geometric-float').forEach((el, index) => {
      if (this.isInViewport(el)) {
        const speed = 0.5 + (index * 0.1);
        el.style.transform = `translateY(${scrollY * speed * 0.1}px)`;
      }
    });

    // ヘッダーの背景変化
    const header = document.querySelector('header');
    if (header) {
      if (scrollY > 100) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    }
  }

  /**
   * リサイズ処理
   */
  handleResize() {
    // モバイル判定の更新
    this.isMobile = window.innerWidth < 768;
    
    // アニメーション設定の再設定が必要な場合
    if (this.isMobile) {
      // モバイルでは重いアニメーションを軽減
      document.querySelectorAll('.geometric-float').forEach(el => {
        el.style.animationDuration = '10s';
      });
    }
  }

  /**
   * アクセシビリティ対応
   */
  setupAccessibility() {
    // フォーカス表示の改善
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Tab') {
        document.body.classList.add('keyboard-navigation');
      }
    });

    document.addEventListener('mousedown', () => {
      document.body.classList.remove('keyboard-navigation');
    });

    // スクリーンリーダー用の動的コンテンツ通知
    this.setupAriaLiveRegions();
  }

  /**
   * ARIA Live Region の設定
   */
  setupAriaLiveRegions() {
    // 動的コンテンツ変更の通知用
    if (!document.getElementById('aria-live-region')) {
      const liveRegion = document.createElement('div');
      liveRegion.id = 'aria-live-region';
      liveRegion.setAttribute('aria-live', 'polite');
      liveRegion.setAttribute('aria-atomic', 'true');
      liveRegion.style.position = 'absolute';
      liveRegion.style.left = '-10000px';
      liveRegion.style.width = '1px';
      liveRegion.style.height = '1px';
      liveRegion.style.overflow = 'hidden';
      document.body.appendChild(liveRegion);
    }
  }

  /**
   * 要素がビューポート内にあるかチェック
   */
  isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
      rect.top >= 0 &&
      rect.left >= 0 &&
      rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
      rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
  }

  /**
   * アニメーション無効時の処理
   */
  showAllElements() {
    document.querySelectorAll('.fade-in-up').forEach(el => {
      el.classList.add('visible');
      el.style.opacity = '1';
      el.style.transform = 'translateY(0)';
    });
  }

  /**
   * メモリリークを防ぐクリーンアップ
   */
  destroy() {
    this.observers.forEach(observer => {
      observer.disconnect();
    });
    this.observers.clear();
    this.animatedElements.clear();
  }
}

// フォーム関連のアニメーション
class FormAnimations {
  static init() {
    // フォーム入力時のアニメーション
    document.querySelectorAll('input, select, textarea').forEach(input => {
      input.addEventListener('focus', (e) => {
        const parent = e.target.closest('.form-group') || e.target.parentElement;
        parent.classList.add('focused');
      });

      input.addEventListener('blur', (e) => {
        const parent = e.target.closest('.form-group') || e.target.parentElement;
        parent.classList.remove('focused');
      });

      // 入力値がある場合のスタイル
      input.addEventListener('input', (e) => {
        const parent = e.target.closest('.form-group') || e.target.parentElement;
        if (e.target.value.trim()) {
          parent.classList.add('has-value');
        } else {
          parent.classList.remove('has-value');
        }
      });
    });
  }
}

// ページ読み込み時の初期化処理
class PageLoadAnimations {
  static init() {
    // ローディングアニメーションの処理
    const loadingScreen = document.getElementById('loading-screen');
    if (loadingScreen) {
      window.addEventListener('load', () => {
        setTimeout(() => {
          loadingScreen.style.opacity = '0';
          setTimeout(() => {
            loadingScreen.style.display = 'none';
          }, 500);
        }, 800);
      });
    }

    // ページ遷移時のアニメーション
    document.querySelectorAll('a[href^="/"]').forEach(link => {
      link.addEventListener('click', (e) => {
        // 外部リンクや特別な処理が必要なリンクは除外
        if (link.target === '_blank' || link.href.includes('#')) {
          return;
        }

        // ページ遷移アニメーション（任意）
        document.body.classList.add('page-transition');
      });
    });
  }
}

// 初期化
document.addEventListener('DOMContentLoaded', () => {
  // メインアニメーションコントローラー
  window.animationController = new AnimationController();
  
  // フォームアニメーション
  FormAnimations.init();
  
  // ページ読み込みアニメーション
  PageLoadAnimations.init();
});

// パフォーマンス監視（開発時のみ）
if (process.env.NODE_ENV === 'development') {
  // アニメーションのパフォーマンスを監視
  const observer = new PerformanceObserver((list) => {
    list.getEntries().forEach((entry) => {
      if (entry.entryType === 'measure' && entry.name.includes('animation')) {
        console.log(`Animation ${entry.name}: ${entry.duration}ms`);
      }
    });
  });
  observer.observe({ entryTypes: ['measure'] });
}

// エクスポート（必要に応じて）
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { AnimationController, FormAnimations, PageLoadAnimations };
}