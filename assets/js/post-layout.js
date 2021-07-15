function jankx_post_layout_tab_links_create_trigger(link_elements) {
    for (i = 0; i < link_elements.length; i++) {
        var link_element = link_elements[i];
        link_element.addEventListener('click', jankx_post_layout_tab_link_click_event);
    }
}

/**
 *
 * @param {HTMLElement} e
 */
function jankx_post_layout_tab_link_click_event(e) {
    e.preventDefault();
    var clickedTab = e.target.parent();
    var tabsWrap = clickedTab.findParent('.jankx-tabs');
    if (tabsWrap.hasClass('blocked')) {
        return;
    }

    var data_type = clickedTab.dataset.type;
    var data_type_name = clickedTab.dataset.typeName;
    var object_id = clickedTab.dataset.objectId;
    if (!(data_type && data_type_name && object_id)) {
        return;
    }

    var currentActiveTab = tabsWrap.find('.the-tab.active');
    var parentTabWrap = tabsWrap.findParent('.jankx-parent-layout');
    var contentLayout = parentTabWrap.find('.jankx-post-layout-wrap');
    if (!contentLayout) {
        return;
    }

    tabsWrap.addClass('blocked');
    currentActiveTab.removeClass('active');
    clickedTab.addClass('active');

    var post_type = contentLayout.dataset.postType;
    var current_page = contentLayout.dataset.currentPage || 1;
    var posts_per_page = contentLayout.dataset.postsPerPage || 10;
    var layout = contentLayout.dataset.layout || 'card';
    var engine_id = contentLayout.dataset.engineId;

    if (!post_type || !engine_id) {
        tabsWrap.removeClass('blocked');
        return;
    }

    var body = {
        action: jkx_post_layout.action,
        data_type: data_type,
        type_name: data_type_name,
        object_id: object_id,
        post_type: post_type,
        current_page: current_page,
        posts_per_page: posts_per_page,
        layout: layout,
        engine_id: engine_id,
    }

    jankx_ajax(jkx_post_layout.ajax_url, 'GET', body, {
        beforeSend: function() {
            tabsWrap.find('.active');
        },
        complete: function(xhr) {
            var jankx_post_wrap = contentLayout.find('.jankx-posts');
            var mode = jankx_post_wrap.dataset.mode || 'append';

            if (xhr.readyState === 4 && xhr.status === 200) {
                var success_flag = xhr.responseJSON && xhr.responseJSON.success;

                // Success case
                if (success_flag) {
                    var realContentWrap = jankx_post_wrap.dataset.contentWrapper
                        ? jankx_post_wrap.find(jankx_post_wrap.dataset.contentWrapper)
                        : jankx_post_wrap;

                    if (mode === 'replace') {
                        realContentWrap.html(xhr.responseJSON.data.content);
                    } else {
                        realContentWrap.appendHTML(xhr.responseJSON.data.content);
                    }
                }

                // Support callback
                var jankx_callback = jkx_post_layout['jankx_tabs_' + engine_id + '_' + layout  + '_' + post_type];
                if (window[jankx_callback]) {
                    window[jankx_callback](realContentWrap, body, success_flag);
                }

                tabsWrap.removeClass('blocked');
            }
        }
    });
}

function jankx_post_layout_init() {
    var post_layout_tab_links = document.querySelectorAll('.jankx-tabs.post-layout-tabs>li>a');
    jankx_post_layout_tab_links_create_trigger(post_layout_tab_links);
}

document.addEventListener(
    'DOMContentLoaded',
    jankx_post_layout_init
);
