<?php 
// Sidebars
add_filter('sidebars_top_tabs_saved', 'sidebars_top_tabs_saved_sidebars');
function sidebars_top_tabs_saved_sidebars ($d) {$d['sidebars'] = maybe_unserialize('a:4:{s:11:"30fs6yk453s";a:7:{s:5:"label";s:15:"Default Sidebar";s:5:"alias";s:7:"default";s:3:"key";s:11:"30fs6yk453s";s:5:"index";s:11:"30fs6yk453s";s:12:"ancestor_key";s:0:"";s:11:"version_key";s:20:"id_jn5fs773hgxfjz14w";s:10:"import_key";s:0:"";}s:12:"34rkyn956ukg";a:7:{s:5:"label";s:17:"Home Page Sidebar";s:5:"alias";s:9:"home-page";s:3:"key";s:12:"34rkyn956ukg";s:5:"index";s:12:"34rkyn956ukg";s:12:"ancestor_key";s:0:"";s:11:"version_key";s:20:"id_ijf7c541wcxb0wqcc";s:10:"import_key";s:0:"";}s:11:"jydt2hxsqa8";a:7:{s:5:"label";s:12:"Page Sidebar";s:5:"alias";s:12:"default-page";s:3:"key";s:11:"jydt2hxsqa8";s:5:"index";s:11:"jydt2hxsqa8";s:12:"ancestor_key";s:0:"";s:11:"version_key";s:20:"id_v919g1e9nrhm3zwf4";s:10:"import_key";s:0:"";}s:12:"292lzscgo3tw";a:7:{s:5:"label";s:12:"Post Sidebar";s:5:"alias";s:12:"post-sidebar";s:3:"key";s:12:"292lzscgo3tw";s:5:"index";s:12:"292lzscgo3tw";s:12:"ancestor_key";s:0:"";s:11:"version_key";s:20:"id_y09il71t1axarmc4c";s:10:"import_key";s:0:"";}}', true); return $d; }

// Tabs
add_filter('sidebars_top_tabs_saved', 'sidebars_top_tabs_saved_tabs');
function sidebars_top_tabs_saved_tabs ($d) {$d['tabs'] = maybe_unserialize('a:2:{s:12:"4e4od16scosg";a:10:{s:5:"label";s:7:"SIGN IN";s:5:"class";s:0:"";s:10:"conditions";s:27:"-function-is-user-logged-in";s:8:"bg_color";s:0:"";s:5:"alias";s:12:"4e4od16scosg";s:3:"key";s:12:"4e4od16scosg";s:5:"index";s:12:"4e4od16scosg";s:12:"ancestor_key";s:0:"";s:11:"version_key";s:20:"id_jkksy6ydgcfhwu8kc";s:10:"import_key";s:0:"";}s:12:"4lct468lzl6o";a:10:{s:5:"label";s:8:"REGISTER";s:5:"class";s:11:"primary-tab";s:10:"conditions";s:27:"-function-is-user-logged-in";s:8:"bg_color";s:0:"";s:5:"alias";s:12:"4lct468lzl6o";s:3:"key";s:12:"4lct468lzl6o";s:5:"index";s:12:"4lct468lzl6o";s:12:"ancestor_key";s:0:"";s:11:"version_key";s:20:"id_sy6sq1w58npvz64n4";s:10:"import_key";s:0:"";}}', true); return $d; }
?>