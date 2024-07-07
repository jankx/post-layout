(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else {
		var a = factory();
		for(var i in a) (typeof exports === 'object' ? exports : root)[i] = a[i];
	}
})(self, () => {
return /******/ (() => { // webpackBootstrap
/******/ 	// The require scope
/******/ 	var __webpack_require__ = {};
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other entry modules.
(() => {
/*!***********************************!*\
  !*** ./assets/src/post-layout.js ***!
  \***********************************/
/**
 *
 * @param {NodeList} link_elements
 */
function jankxPostLayoutCreateTabLinksTrigger(link_elements) {
  for (i = 0; i < link_elements.length; i++) {
    var link_element = link_elements[i];
    link_element.addEventListener('click', jankxPostLayoutTabLinkClickEvent);
  }
}
function jankxPostLayoutCreateLoadMoreButtonTrigger(buttons) {
  for (i = 0; i < buttons.length; i++) {
    var moreMoreButton = buttons[i];
    moreMoreButton.addEventListener('click', jankxPostLayoutTabLinkClickEvent);
  }
}

/**
 *
 * @param {HTMLElement} e
 */
function jankxPostLayoutTabLinkClickEvent(e) {
  e.preventDefault();
  var target = e.target;
  var isLoadMore = target.dataset.loadMore || false;

  // Get clicked element
  var clickedTab = isLoadMore ? target : target.parent();
  var contentLayout = null;

  // Tabs objects
  var tabsWrap = null;
  var dynamicData = {};

  // Tab item click event
  if (!isLoadMore) {
    tabsWrap = clickedTab.findParent('.jankx-tabs');
    if (tabsWrap.hasClass('blocked')) {
      return;
    }
    var data_type = clickedTab.dataset.type;
    var data_type_name = clickedTab.dataset.typeName;
    var object_id = clickedTab.dataset.objectId;
    if (!(data_type && data_type_name && object_id)) {
      return;
    }
    Object.assign(dynamicData, {
      data_type: data_type,
      type_name: data_type_name,
      object_id: object_id
    });
    var currentActiveTab = tabsWrap.find('.the-tab.active');
    var parentTabWrap = tabsWrap.findParent('.jankx-parent-layout');
    contentLayout = parentTabWrap.find('.jankx-post-layout-wrap');
    tabsWrap.addClass('blocked');
    currentActiveTab.removeClass('active');
    clickedTab.addClass('active');
  } else {
    // This case for load more button
    contentLayout = document.getElementById(clickedTab.dataset.loadMore);
  }
  if (!contentLayout) {
    return;
  }
  var post_type = contentLayout.dataset.postType;
  var posts_per_page = contentLayout.dataset.postsPerPage || 10;
  var layout = contentLayout.dataset.layout || 'card';
  var engine_id = contentLayout.dataset.engineId;
  var thumb_pos = contentLayout.dataset.thumbnailPosition;
  var thumb_size = contentLayout.dataset.thumbnailSize || 'medium';
  var data_preset = contentLayout.dataset.preset;
  if (!post_type || !engine_id) {
    if (tabsWrap) {
      tabsWrap.removeClass('blocked');
    }
    return;
  }
  if (isLoadMore) {
    dynamicData.posts_per_page = clickedTab.dataset.loadItems;
    dynamicData.offset = posts_per_page;
  } else {
    dynamicData.posts_per_page = posts_per_page;
    dynamicData.current_page = contentLayout.dataset.currentPage || 1;
  }
  var jankx_post_wrap = contentLayout.find('.jankx-posts');
  var tax_query = jankx_post_wrap.dataset.tax_query;
  if (tax_query) {
    dynamicData.tax_query = JSON.stringify(tax_query);
  }
  var body = {
    action: jkx_post_layout.action,
    post_type: post_type,
    layout: layout,
    engine_id: engine_id,
    thumb_pos: thumb_pos,
    thumb_size: thumb_size
  };
  if (data_preset) {
    body.data_preset = data_preset;
  }
  body = Object.assign(body, dynamicData);
  jankx_ajax(jkx_post_layout.ajax_url, 'GET', body, {
    beforeSend: function beforeSend() {},
    complete: function complete(xhr) {
      var mode = jankx_post_wrap.dataset.mode || 'append';
      if (xhr.readyState === 4 && xhr.status === 200) {
        var response = xhr.responseJSON || {};
        var success_flag = response.success || false;

        // Success case
        if (success_flag) {
          var data = response.data || {};
          var realContentWrap = jankx_post_wrap.dataset.contentWrapper ? jankx_post_wrap.find(jankx_post_wrap.dataset.contentWrapper) : jankx_post_wrap;
          if (mode === 'replace') {
            realContentWrap.html(data.content);
          } else {
            realContentWrap.appendHTML(data.content);
          }
          if (['carousel', 'preset-3', 'preset-5'].indexOf(layout) >= 0) {
            swiffyslider.init();
          }
          if (data.next_offset > 0) {
            contentLayout.setAttribute('data-posts-per-page', data.next_offset);
          }
        }

        // Support callback
        var jankx_callback = jkx_post_layout['jankx_tabs_' + engine_id + '_' + layout + '_' + post_type];
        if (window[jankx_callback]) {
          window[jankx_callback](realContentWrap, body, success_flag);
        }
        if (tabsWrap) {
          tabsWrap.removeClass('blocked');
        }
      }
    }
  });
}

/**
 *
 * @param {FsLightbox} lightboxInstance
 * @param {NodeList} sources
 */
function jankxPostLayoutSetupLightboxSources(lightboxInstance, sources) {
  lightboxInstance.props.sources = [];
  lightboxInstance.props.currentIndex = 0;
  sources.forEach(function (source) {
    var dataset = source.dataset || {};
    if (!dataset.src) {
      return;
    }
    var galleryIndex = dataset.galleryIndex ? parseInt(dataset.galleryIndex) : 0;
    if (galleryIndex != lightboxInstance.props.currentIndex) {
      source.setAttribute('data-gallery-index', lightboxInstance.props.currentIndex);
    }
    source.addEventListener('click', function (e) {
      var tag = e.target;
      var target = tag.tagName === 'A' ? tag.querySelector('has-lightbox') : tag.findParent('.has-lightbox');
      var galleryIndex = target.dataset.galleryIndex ? parseInt(target.dataset.galleryIndex) : 0;
      lightboxInstance.open(galleryIndex);
    });
    lightboxInstance.props.sources.push(dataset.src);
    lightboxInstance.props.currentIndex += 1;
  });
}
function jankxPostLayoutSetupLightbox() {
  // Get all post layouts
  var jankxPostLayouts = document.querySelectorAll('.jankx-post-layout-wrap');
  for (i = 0; i < jankxPostLayouts.length; i++) {
    var jankxPostLayout = jankxPostLayouts[i];
    var lightboxes = jankxPostLayout.querySelectorAll('.has-lightbox');
    if (lightboxes.length <= 0) {
      continue;
    }
    window[camelize(jankxPostLayout.getAttribute('id'))] = new FsLightbox();
    jankxPostLayoutSetupLightboxSources(window[camelize(jankxPostLayout.getAttribute('id'))], lightboxes);
  }
}
window['jankxCarouselTabs'] = {};
/**
 *
 * @param {HTMLElement} tabCarousel
 */
function jankxSetupMobileCarouselList(tabCarousel) {
  var listItems = tabCarousel.querySelectorAll('li.the-tab');
  for (var i = 0; i < listItems.length; i++) {
    listItems[i].addClass('splide__slide');
  }
}
function jankxSetupMobileCarousel(tabCarousel) {
  var tabs = tabCarousel.querySelector('ul.post-layout-tabs');
  jankxSetupMobileCarouselList(tabCarousel);
  tabs.removeClass('post-layout-tabs');
  tabs.addClass('splide__list');
  carouselHTML = '<div class="tabs-carousel-wrap"><div class="splide__track">' + tabs.outerHTML + '</div></div>';
  tabs.outerHTML = carouselHTML;
  window.jankxCarouselTabs['tabsCarouselWrap' + i] = new Splide(tabCarousel.querySelector('.tabs-carousel-wrap'), {
    perPage: tabCarousel.dataset.columns || 2,
    arrows: true,
    pagination: false
  });
  window.jankxCarouselTabs['tabsCarouselWrap' + i].mount();
  tabCarousel.addClass('is-tabs-carousel');
}

/**
 *
 * @param {HTMLElement} tabCarousel
 */
function jankxDestroyMobileCarouselList(tabCarousel) {
  var listItems = tabCarousel.querySelectorAll('li.the-tab');
  for (var i = 0; i < listItems.length; i++) {
    listItems[i].removeClass('splide__slide');
  }
}
/**
 *
 * @param {HTMLElement} tabCarousel
 */
function jankxDestroyMobileCarousel(tabCarousel) {
  tabCarousel.removeClass('is-tabs-carousel');
  if (window.jankxCarouselTabs['tabsCarouselWrap' + i]) {
    window.jankxCarouselTabs['tabsCarouselWrap' + i].destroy();
  }
  var tabs = tabCarousel.querySelector('ul.splide__list');
  tabs.addClass('post-layout-tabs');
  tabs.removeClass('splide__list');
  var tabsCarouselWrap = tabCarousel.querySelector('.tabs-carousel-wrap');
  tabsCarouselWrap.outerHTML = tabs.outerHTML;
  jankxDestroyMobileCarouselList(tabCarousel);
}
function jankxMakeTabsIsCarouselOnMobile() {
  var tabCarousels = document.querySelectorAll('[data-tab-carousel]');
  var jankxConfigs = window.jankx || {};
  breakpoint = jankxConfigs['mobile_breakpoint'] || 600;
  isMobile = window.innerWidth <= breakpoint;
  for (i = 0; i < tabCarousels.length; i++) {
    var tabCarousel = tabCarousels[i];
    hasTrackList = tabCarousel.querySelector('.tabs-carousel-wrap');
    if (isMobile) {
      if (hasTrackList) {
        continue;
      }
      jankxSetupMobileCarousel(tabCarousel);
    } else {
      if (!hasTrackList) {
        continue;
      }
      jankxDestroyMobileCarousel(tabCarousel);
    }
    jankxPostLayoutCreateTabLinksTrigger(tabCarousel.querySelectorAll('.the-tab.has-event a'));
  }
}
function jankx_post_layout_init() {
  var post_layout_tab_links = document.querySelectorAll('.jankx-tabs.post-layout-tabs>li.has-event>a');
  jankxPostLayoutCreateTabLinksTrigger(post_layout_tab_links);
  var moreMoreButtons = document.querySelectorAll('[data-load-more]');
  jankxPostLayoutCreateLoadMoreButtonTrigger(moreMoreButtons);
  jankxPostLayoutSetupLightbox();
  var tabCarousels = document.querySelectorAll('[data-tab-carousel]');
  if (tabCarousels.length > 0) {
    jankxMakeTabsIsCarouselOnMobile();
    window.addEventListener('resize', jankxMakeTabsIsCarouselOnMobile);
  }
}
document.addEventListener('DOMContentLoaded', jankx_post_layout_init);
window.addEventListener("load", function () {
  swiffyslider.init();
});
})();

// This entry need to be wrapped in an IIFE because it need to be in strict mode.
(() => {
"use strict";
/*!**************************************!*\
  !*** ./assets/scss/post-layout.scss ***!
  \**************************************/
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin

})();

/******/ 	return __webpack_exports__;
/******/ })()
;
});
//# sourceMappingURL=post-layout.js.map