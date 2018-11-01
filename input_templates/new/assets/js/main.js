const knot =(n={})=>{const t=Object.create(null);function e(n,e){return t[n]=t[n]||[],t[n].push(e),this}function c(n,e=!1){return e?t[n].splice(t[n].indexOf(e),1):delete t[n],this}return{...n,on:e,once:function(n,t){return t._once=!0,e(n,t),this},off:c,emit:function(n,...e){const o=t[n]&&t[n].slice();return o&&o.forEach(t=>{t._once&&c(n,t),t.apply(this,e)}),this}}};
const Layzr =(t={})=>{let e,r,n,i=l();const o={normal:t.normal||"data-normal",retina:t.retina||"data-retina",srcset:t.srcset||"data-srcset",threshold:t.threshold||0},s=document.body.classList.contains("srcset")||"srcset"in document.createElement("img"),c=window.devicePixelRatio||window.screen.deviceXDPI/window.screen.logicalXDPI,a=knot({handlers:function(t){const e=t?"addEventListener":"removeEventListener";return["scroll","resize"].forEach(t=>window[e](t,u)),this},check:f,update:h});return a;function l(){return window.scrollY||window.pageYOffset}function u(){i=l(),e||(window.requestAnimationFrame(()=>f()),e=!0)}function d(t){const e=i,r=e+n,s=function(t){return t.getBoundingClientRect().top+i}(t),c=s+t.offsetHeight,a=o.threshold/100*n;return c>=e-a&&s<=r+a}function f(){return n=window.innerHeight,r.forEach(t=>d(t)&&function(t){if(a.emit("src:before",t),s&&t.hasAttribute(o.srcset))t.setAttribute("srcset",t.getAttribute(o.srcset));else{const e=c>1&&t.getAttribute(o.retina);t.setAttribute("src",e||t.getAttribute(o.normal))}a.emit("src:after",t),[o.normal,o.retina,o.srcset].forEach(e=>t.removeAttribute(e)),h()}(t)),e=!1,this}function h(){return r=Array.prototype.slice.call(document.querySelectorAll(`[${o.normal}]`)),this}};

document.addEventListener("DOMContentLoaded", function() {
  var main = new MainModule();
  main.init();
});

const VARS = {
  MOBILE_WIDTH: 609
}

function MainModule() {
  var self = this;

  this.isOpenFilterList = false;
  this.isMobile = () => window.innerWidth <= VARS.MOBILE_WIDTH;

  this.DOM = {};

  this.DOM.title = document.querySelector('.title-js');

  this.DOM.filterBtn = document.querySelector('.vertical-dots');
  this.DOM.filterList = document.querySelector('.drop-down');

  this.titleText = this.DOM.title ? this.DOM.title.innerText : '';
  this.titleLength = this.titleText.length;

  this.isTagPage = !!this.DOM.title;
  this.isIndex = !!document.querySelector('.index-js');

  $(document).on('click', '.post--video .play-screen', this.playClick);
  $(document).on('click', '.post--gif .post__content', this.gifClickHandler);

  this.DOM.filterBtn && this.DOM.filterBtn.addEventListener('click', this.toggleFilters.bind(null, this))
}

MainModule.prototype.playClick = function(event) {
  event.currentTarget.classList.add('play-screen--hide');
  const video = event.currentTarget.parentNode.querySelector('video');
  const play = video.getAttribute('data-play');
  video.play();

  if (!play) {
    video.setAttribute('data-play', 'true');
    video.parentNode.querySelector('.post__link').classList.remove('post__link--hidden');
  }
};

MainModule.prototype.gifClickHandler = function(event) {
  const el = event.currentTarget;
  const gifEl = el.querySelector('img');
  const source = gifEl.getAttribute('data-src');
  const play = gifEl.getAttribute('data-play');
  const spinner = el.querySelector('.spinner-wrapper');
  el.setAttribute('data-stoped', 'false');

  spinner.classList.remove("hidden");

  if (!play) {
    gifEl.setAttribute('data-play', 'true');
    gifEl.setAttribute('src', source);
    gifEl.addEventListener('load', function() { 
      spinner.classList.add("hidden");
    }, false);
    gifEl.parentNode.querySelector('.post__link').classList.remove('post__link--hidden');
  }
};

MainModule.prototype.toggleFilters = (self) => {
  event.stopPropagation();

  self.DOM.filterList.classList.remove('drop-down--hide')
  self.isOpenFilterList = true;
};

MainModule.prototype.getPath = function() {
  const currentPage = +document.querySelector('.page-js').getAttribute('data-current-page');
  let path = '';

  if (window.location.pathname === '/') {
    path = `page${currentPage + this.loadCount + 1}.html`
  } else {
    path = window.location.pathname.replace(
      new RegExp('(-[1-9]{1,3})?.html$'), `${currentPage + this.loadCount + 1}.html`
    );
    path = path.replace('index','page');
  }

  return path;
};

MainModule.prototype.handleDisableJs = function() {
  document.querySelectorAll('.js-work').forEach(item => {
    item.classList.remove('js-work');
  });

  document.querySelectorAll('.no-js').forEach(item => {
    item.style.display = 'none';
  });

  const content = document.querySelector('.content-js');

  if (content) {
    content.classList.add('content--loading');
    content.classList.remove('content--no-js');
  }

};

MainModule.prototype.prepearForLazyLoading = function() {
  $('.lazy-img-js').Lazy({
    afterLoad: function(element) {
      $(element).parent('.post__content').find('.spinner-wrapper').remove();
    },
    onError: function(element) {
      $(element).parent('.post__content').find('.spinner-wrapper').remove();
    }
  });
  // document.querySelectorAll('.lazy-img-js').forEach(item => {
  //   item.setAttribute('data-normal', item.getAttribute('src'));
  //   item.setAttribute('src', '');
  // })
};

MainModule.prototype.checkTitle = function() {
  const divider = this.isMobile() ? 10 : 13;
  const width = this.DOM.title.offsetWidth;
  const ratio = width / divider;

  this.DOM.title.innerText = ratio < this.titleLength
    ? this.titleText.substr(0, ratio) + '...'
    : this.titleText;
};


MainModule.prototype.init = function() {

  this.handleDisableJs();

  this.prepearForLazyLoading();

  window.addEventListener('click', (e) => {
    if (this.DOM.filterList && !this.DOM.filterList.contains(e.target)) {
      this.DOM.filterList.classList.add('drop-down--hide')
    }
  });

  if (this.isTagPage) {
    this.checkTitle();
    window.addEventListener("resize", () => this.checkTitle.call(this) );
  }

  var grid = document.querySelector('.content');

  // var msnry = !this.isMobile() && grid && new Masonry( grid, {
  //   itemSelector: 'none',
  //   columnWidth: '.post',
  //   fitWidth: true,
  //   gutter: 10,
  //   startOffset: 0
  // });

  grid && imagesLoaded( grid, () => {
    if (this.isMobile()) {
      grid.classList.add('content--no-js');
    } else {
      // msnry.options.itemSelector = '.post';
      // var items = grid.querySelectorAll('.post');
      // msnry.appended( items );
    }

    grid.classList.remove('content--loading');

    var mySwiper = new Swiper('.swiper-container', {
      speed: 400,
      calculateHeight:true,
      setWrapperSize:true,
      spaceBetween: 0,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev'
      },
      loop: true,
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
    });
  });

  if (this.isTagPage || this.isIndex) {
    var infScroll = new InfiniteScroll( grid, {
      path: this.getPath,
      append: '.post:not(.post--info-block)',
      history: false,
      // outlayer: msnry,
      status: '.page-load-status',
    });
    infScroll.on('append', () => {
      this.prepearForLazyLoading();
      this.instance && this.instance.update();
    });
  }

  this.instance = Layzr({
    threshold: 100
  });

  this.instance
  .update()
  .check()
  .handlers(true);

  this.instance.on('src:after', (element) => {
  });

  this.instance.on('src:before', (element) => {
  })
};