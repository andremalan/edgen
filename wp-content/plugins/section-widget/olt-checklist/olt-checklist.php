<?php

/*
    OLT Checklist Helper Functions
    
    Version 1.1.4
*/

if(!function_exists('olt_checklist')) { 
    
    /*
        $args = array(
            'echo' => true|false,       echo out or just return a string
            'id'   => 'string',         base id for elements
            'name' => 'string',         base name for form elements
            'exclude' => array(),       exclude certain tabs
            'special-pages' => array(), options for the special pages tab
            'pages' => array(),         options for the pages tab
            'categories' => array(),    options for the categories tab
            'tags' => array()           options for the tags tab
        );
    */
    function olt_checklist_pane($args = array()) {
        // Process top-level defaults first
        $defaults = array(
            'echo' => true,
            'id' => 'olt-checklist',
            'name' => 'olt-checklist',
            'exclude' => array()
        );
        
        $args = wp_parse_args( $args, $defaults );
        
        // Set default name/id for second-level...
        
        // Special Pages (id and name only, set others in the actual function)
        $defaults = array(
            'id' => $args['id'].'-special-pages',
            'name' => $args['name'].'[special-pages]'
        );
        $args['special-pages'] = wp_parse_args( $args['special-pages'], $defaults );
        
        // Pages (id and name only, set others in the actual function)
        $defaults = array(
            'id' => $args['id'].'-pages',
            'name' => $args['name'].'[pages]'
        );
        $args['pages'] = wp_parse_args( $args['pages'], $defaults );
        
        // Categories (id and name only, set others in the actual function)
        $defaults = array(
            'id' => $args['id'].'-categories',
            'name' => $args['name'].'[categories]'
        );
        $args['categories'] = wp_parse_args( $args['categories'], $defaults );
        
        // Tags (id and name only, set others in the actual function)
        $defaults = array(
            'id' => $args['id'].'-tags',
            'name' => $args['name'].'[tags]'
        );
        $args['tags'] = wp_parse_args( $args['tags'], $defaults );
                        
        // Supress output, we want to control the echo-ing here instead
        $args['special-pages']['echo'] = false;
        $args['pages']['echo'] = false;
        $args['categories']['echo'] = false;
        $args['tags']['echo'] = false;
                
        extract( $args, EXTR_SKIP );
        
        $output  = "<div id=\"{$id}-wrapper\" class=\"olt-checklist-wrapper\">\n";
        $output .= "  <ul>\n";
        if(!in_array('special-pages', $exclude))
            $output .= "    <li><a href=\"#{$id}-special-pages\">Special Pages</a></li>\n";
        if(!in_array('pages', $exclude))
            $output .= "    <li><a href=\"#{$id}-pages\">Pages</a></li>\n";
        if(!in_array('categories', $exclude))
            $output .= "    <li><a href=\"#{$id}-categories\">Categories</a></li>\n";
        if(!in_array('tags', $exclude))
            $output .= "    <li><a href=\"#{$id}-tags\">Tags</a></li>\n";
        $output .= "  </ul>\n";
        
        if(!in_array('special-pages', $exclude)) {
            $output .= "  <div id=\"{$id}-special-pages\">\n";
            $output .= olt_checklist('special-pages', $args['special-pages']);
            $output .= "  </div>\n";
        }
        
        if(!in_array('pages', $exclude)) {
            $output .= "  <div id=\"{$id}-pages\">\n";
            $output .= "  <p class=\"olt-checklist-tab-description\">Display this widget on these pages.</p>\n";
            $output .= olt_checklist('pages', $args['pages']);
            $output .= "  </div>\n";
        }
        
        if(!in_array('categories', $exclude)) {
            $output .= "  <div id=\"{$id}-categories\">\n";
            $output .= "  <p class=\"olt-checklist-tab-description\">Display this widget on posts or pages filed under these categories.</p>\n";
            $output .= olt_checklist('categories', $args['categories']);
            $output .= "  </div>\n";
        }
        
        if(!in_array('tags', $exclude)) {
            $output .= "  <div id=\"{$id}-tags\">\n";
            $output .= "  <p class=\"olt-checklist-tab-description\">Display this widget on posts or pages with these tags.</p>\n";
            $output .= olt_checklist('tags', $args['tags']);
            $output .= "  </div>\n";
        }
        
        $output .= "</div>\n";
        
        if ( $echo ) echo $output;
        
        return $output;
    }
    
    function olt_checklist($mode = 'pages', $args = '') {
        switch($mode) {
            case 'special-pages':
                $defaults = array( 'selected' => array(), 'echo' => 1, 'id' => 'olt-checklist-special-pages', 'name' => 'olt-checklist-special-pages', 'exclude' => array());
                $data = array(
                    (object) array( 'id' => 'all', 'name' => 'Everywhere',
                                    'description' => 'Display this widget everywhere on your site, i.e. behave like any other widgets.'),
                    (object) array( 'id' => 'front', 'name' => 'Front Page',
                                    'description' => 'Display this widget on the front page, as determined by your site\'s <a href="'.admin_url('options-reading.php').'">Reading Settings</a>.'),
                    (object) array( 'id' => 'home', 'name' => 'Posts page',
                                    'description' => 'Display this widget on the posts page (i.e. your blog). This is usually your front page, but it can be changed in the  <a href="'.admin_url('options-reading.php').'">Reading Settings</a>.'),
                    (object) array('id' => 'category', 'name' => 'Selected Category Archive Pages',
                                   'description' => 'Display this widget on a category archive page if the corresponding category is selected in the <strong>Categories</strong> tab.'),
                    (object) array('id' => 'tag', 'name' => 'Selected Tag Archive Pages',
                                   'description' => 'Display this widget on a tag archive page if the corresponding tag is selected in the <strong>Tags</strong> tab.'),
                    (object) array('id' => 'allcategory', 'name' => 'All Category Archive Pages',
                                   'description' => 'Display this widget on all category archive pages.'),
                    (object) array('id' => 'alltag', 'name' => 'All Tag Archive Pages',
                                   'description' => 'Display this widget on all tag archive pages.'),
                    (object) array('id' => 'date', 'name' => 'All Date-Based Archive Pages',
                                   'description' => 'Display this widget on all date-based archive pages (i.e. all monthly, yearly, daily or time-based archives).'),
                    (object) array('id' => 'page', 'name' => 'All Pages', 'description' => 'Display this widget on all pages.'),
                    (object) array('id' => 'post', 'name' => 'All Post', 'description' => 'Display this widget on all posts.'),
                    (object) array('id' => 'comment', 'name' => 'Commentable Pages/Posts', 'description' => 'Display this widget on all pages/posts where comments are allowed.'),
                    (object) array('id' => 'author', 'name' => 'Author Pages', 'description' => 'Display this widget on all author pages.'),
                    (object) array('id' => 'search', 'name' => 'Search Results', 'description' => 'Display this widget on all search result pages.'),
                    (object) array('id' => 'notfound', 'name' => '&quot;Not Found&quot; Pages', 'description' => 'Display this widget when a requested page cannot be found.')
                );
                $walker = new OLTSpecialPagesChecklistWalker;
                break;
            
            case 'categories':
                $defaults = array( 'selected' => array(), 'echo' => 1, 'id' => 'olt-checklist-categories', 'name' => 'olt-checklist-categories' );
                $data = get_categories('get=all');
                $walker = new OLTCategoryChecklistWalker;
                break;
            
            case 'tags':
                $defaults = array( 'selected' => array(), 'echo' => 1, 'id' => 'olt-checklist-tags', 'name' => 'olt-checklist-tags' );
                $data = get_tags('get=all');
                $walker = new OLTTagChecklistWalker;
                break;
            
            case 'pages':
            default:
                $defaults = array( 'selected' => array(), 'echo' => 1, 'id' => 'olt-checklist-pages', 'name' => 'olt-checklist-pages' );
                $data = get_pages('get=all');
                $walker = new OLTPageChecklistWalker;
                break;
        }
        
        $args = wp_parse_args( $args, $defaults );
        extract( $args, EXTR_SKIP );
        
        if ( !empty($data) ) {
            $output = "<ul class=\"olt-checklist\" id=\"$id\">\n";
            $output .= $walker->walk($data, 0, $args);
            $output .= "</ul>\n";
        } else {
            $output = "No $mode found.";
        }
        
        if ( $echo ) echo $output;
        
        return $output;
    }
    
    function olt_checklist_conditions_check($conditions) {
        wp_reset_query(); // Just in case
        
        // Most of the conditional tags will always return true when passed an empty array. So we need to pad them will some junk.
        $conditions['special-pages'][] = -1;
        $conditions['pages'][] = -1;
        $conditions['categories'][] = -1;
        $conditions['tags'][] = -1;
        
        foreach($conditions['special-pages'] as $key) {
            switch($key){
                case "all":
                    return true;
                    break;
                case "front":
                    if(is_front_page())
                        return true;
                    break;
                case "home":
                    if(is_home())
                        return true;
                    break;
                case "category":
                    if(is_category($conditions['categories']))
                        return true;
                    break;
                case "tag":
                    $ts = get_tags(array('hide_empty'=>false,'include' => implode(',',$conditions['tags'])));
                    if(is_tag(array_map(create_function('$t', 'return $t->slug;'),$ts)))
                        return true;
                    break;
                case "allcategory":
                    if(is_category())
                        return true;
                    break;
                case "alltag":
                    if(is_tag())
                        return true;
                    break;
                case "date":
                    if(is_date())
                        return true;
                    break;
                case "page":
                    // Exclude the front page to avoid confusion
                    if(!is_front_page() && is_page())
                        return true;
                    break;
                case "post":
                    if(is_single())
                        return true;
                    break;
                case "comment":
                    // Exclude the front page to avoid confusion
                    if(!is_front_page() && is_singular() && comments_open())
                        return true;
                    break;
                case "author":
                    if(is_author())
                        return true;
                    break;
                case "search":
                    if(is_search())
                        return true;
                    break;
                case "notfound":
                    if(is_404())
                        return true;
                    break;
            }
        }
        
        if(is_page($conditions['pages']))
            return true;    
        
        if(is_singular() && in_category($conditions['categories']))
            return true;
        
        if(is_singular() && has_tag($conditions['tags']))
            return true;
        
        return false;
    }
    
    class OLTSpecialPagesChecklistWalker extends Walker {
    
        var $tree_type = 'special';
        var $db_fields = array ('parent' => 'parent', 'id' => 'id');
        
        function start_el(&$output, $special_page, $depth, $args) {
            extract($args);
            
            if(in_array($special_page->id, $exclude)) return;
            
            $id = "{$id}-{$special_page->id}";
            $name .= '[]';
            $title = esc_html($special_page->name);
            $desc = $special_page->description; // Don't escape HTML here..
                    
            $output .= "<li><div class=\"olt-checklist-entry\"><input type=\"checkbox\" class=\"level-$depth\" id=\"$id\" name=\"$name\" value=\"$special_page->id\"";
            if ( in_array($special_page->id, $selected) )
                $output .= ' checked="checked"';
            $output .= " /> <label for=\"$id\">$title</label><p>$desc</p></div>\n";
        }
    }
    
    class OLTChecklistWalker extends Walker {
        
        function start_lvl(&$output, $depth, $args) {
            $indent = str_repeat("\t", $depth);
            $output .= "$indent<ul class='children'>\n";
        }
        
        function end_lvl(&$output, $depth, $args) {
            $indent = str_repeat("\t", $depth);
            $output .= "$indent</ul>\n";
        }
        
        function end_el(&$output, $page, $depth, $args) {
            $output .= "</li>\n";
        }
    }
    
    class OLTPageChecklistWalker extends OLTChecklistWalker {
    
        var $tree_type = 'page';
        var $db_fields = array ('parent' => 'post_parent', 'id' => 'ID');
        
        function start_el(&$output, $page, $depth, $args) {
            extract($args);
            
            $pad = str_repeat('&nbsp;', $depth * 4);
            $id = "{$id}-{$page->ID}";
            $name .= '[]';
            $title = esc_html($page->post_title);
                            
            $output .= "<li><div class=\"olt-checklist-entry\">$pad<input type=\"checkbox\" class=\"level-$depth\" id=\"$id\" name=\"$name\" value=\"$page->ID\"";
            if ( in_array($page->ID, $selected) )
                $output .= ' checked="checked"';
            $output .= " /> <label for=\"$id\">$title</label></div>\n";
        }
    }
    
    class OLtCategoryChecklistWalker extends OLTChecklistWalker {
    
        var $tree_type = 'category';
        var $db_fields = array ('parent' => 'parent', 'id' => 'term_id');
        
        function start_el(&$output, $category, $depth, $args) {
            extract($args);
            
            $pad = str_repeat('&nbsp;', $depth * 4);
            $id = "{$id}-{$category->term_id}";
            $name .= '[]';
            $title = esc_html($category->name);
            
            $output .= "<li><div class=\"olt-checklist-entry\">$pad<input type=\"checkbox\" class=\"level-$depth\" id=\"$id\" name=\"$name\" value=\"$category->term_id\"";
            if ( in_array($category->term_id, $selected) )
                $output .= ' checked="checked"';
            $output .= " /> <label for=\"$id\">$title</label></div>\n";
        }
    }
    
    class OLTTagChecklistWalker extends Walker {
    
        var $tree_type = 'tag';
        var $db_fields = array ('parent' => 'parent', 'id' => 'term_id');
        
        function start_el(&$output, $tag, $depth, $args) {
            extract($args);
            
            $id = "{$id}-{$tag->term_id}";
            $name .= '[]';
            $title = esc_html($tag->name);
                    
            $output .= "<li><div class=\"olt-checklist-entry\"><input type=\"checkbox\" class=\"level-$depth\" id=\"$id\" name=\"$name\" value=\"$tag->term_id\"";
            if ( in_array($tag->term_id, $selected) )
                $output .= ' checked="checked"';
            $output .= " /> <label for=\"$id\">$title</label></div>\n";
        }
    }
}
?>