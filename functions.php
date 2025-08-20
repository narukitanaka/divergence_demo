<?php
// ============== 投稿タイプ ==============
add_action('init', function () {
  register_post_type('company', [
    'label' => '企業',
    'public' => false, 'show_ui' => true, 'show_in_rest' => true,
    'menu_icon' => 'dashicons-building',
    'supports' => ['title'],
  ]);
  register_post_type('office', [
    'label' => '事業所',
    'public' => false, 'show_ui' => true, 'show_in_rest' => true,
    'menu_icon' => 'dashicons-location',
    'supports' => ['title'],
  ]);
  register_post_type('job', [
    'label' => '求人',
    'public' => true, 'has_archive' => 'jobs',
    'rewrite' => ['slug' => 'jobs', 'with_front' => false],
    'show_in_rest' => true,
    'menu_icon' => 'dashicons-id',
    'supports' => ['title'],
  ]);
});

// ============== メタボックス ==============
add_action('add_meta_boxes', function () {
  // companies：IDのみ
  add_meta_box('company_fields', '企業情報', function($post){
    wp_nonce_field('save_company_fields','company_fields_nonce');
    $src = get_post_meta($post->ID, 'company_id_src', true);
    echo '<p><label>company_id：<input type="text" name="company_id_src" value="'.esc_attr($src).'" class="regular-text"></label></p>';
  }, 'company', 'normal');

  // offices：ID＋都道府県/市区町村/説明
  add_meta_box('office_fields', '事業所情報', function($post){
    wp_nonce_field('save_office_fields','office_fields_nonce');
    $src   = get_post_meta($post->ID, 'office_id_src', true);
    $pref  = get_post_meta($post->ID, 'office_prefecture', true);
    $city  = get_post_meta($post->ID, 'office_city', true);
    $desc  = get_post_meta($post->ID, 'office_desc', true);
    $parent_company = (int) get_post_meta($post->ID, 'office_parent_company', true);

    // 親企業一覧
    $companies = get_posts([
      'post_type'   => 'company',
      'numberposts' => -1,
      'orderby'     => 'title',
      'order'       => 'ASC',
      'post_status' => ['publish','draft','private'],
    ]);

    $prefs = [
      '北海道','青森県','岩手県','宮城県','秋田県','山形県','福島県',
      '茨城県','栃木県','群馬県','埼玉県','千葉県','東京都','神奈川県',
      '新潟県','富山県','石川県','福井県','山梨県','長野県',
      '岐阜県','静岡県','愛知県','三重県',
      '滋賀県','京都府','大阪府','兵庫県','奈良県','和歌山県',
      '鳥取県','島根県','岡山県','広島県','山口県',
      '徳島県','香川県','愛媛県','高知県',
      '福岡県','佐賀県','長崎県','熊本県','大分県','宮崎県','鹿児島県','沖縄県'
    ];
    echo '<p><label>office_id：<input type="text" name="office_id_src" value="'.esc_attr($src).'" class="regular-text"></label></p>';

    // 親企業
    echo '<p><label>親企業：<select name="office_parent_company"><option value="">（選択してください）</option>';
    foreach ($companies as $c) {
      printf('<option value="%d"%s>%s</option>', $c->ID, selected($parent_company, $c->ID, false), esc_html(get_the_title($c)));
    }
    echo '</select></label></p>';

    echo '<p><label>都道府県：<select name="office_prefecture"><option value="">選択してください</option>';
    foreach($prefs as $p){
      printf('<option value="%s"%s>%s</option>', esc_attr($p), selected($pref,$p,false), esc_html($p));
    }
    echo '</select></label></p>';

    echo '<p><label>市区町村：<input type="text" name="office_city" value="'.esc_attr($city).'" class="regular-text"></label></p>';
    echo '<p><label>事業所説明文：<textarea name="office_desc" rows="4" class="large-text">'.esc_textarea($desc).'</textarea></label></p>';
  }, 'office', 'normal');


  // jobs：ID＋求人説明＋（企業1つ／事業所複数）を紐づけ
  add_meta_box('job_fields', '求人情報（説明／関連）', function($post){
    wp_nonce_field('save_job_fields','job_fields_nonce');
    $src    = get_post_meta($post->ID, 'job_id_src', true);
    $desc   = get_post_meta($post->ID, 'job_desc', true);
    $cid    = (int) get_post_meta($post->ID, 'job_company', true);
    $office_ids = (array) get_post_meta($post->ID, 'job_offices', true);
    if (!is_array($office_ids)) $office_ids = [];

    // 企業一覧
    $companies = get_posts([
      'post_type' => 'company',
      'numberposts' => -1,
      'orderby' => 'title',
      'order' => 'ASC',
      'post_status' => ['publish','draft','private'],
    ]);
    // 事業所一覧（選んだ企業に絞る）
    $office_query_args = [
      'post_type'   => 'office',
      'numberposts' => -1,
      'orderby'     => 'title',
      'order'       => 'ASC',
      'post_status' => ['publish','draft','private'],
    ];
    if ($cid) {
      $office_query_args['meta_query'] = [
        ['key' => 'office_parent_company', 'value' => $cid, 'compare' => '=']
      ];
    }
    $offices = get_posts($office_query_args);

    echo '<p><label>job_id：<input type="text" name="job_id_src" value="'.esc_attr($src).'" class="regular-text"></label></p>';
    echo '<p><label>求人説明文：<textarea name="job_desc" rows="6" class="large-text">'.esc_textarea($desc).'</textarea></label></p>';

    echo '<hr><p><strong>企業（1つ選択）</strong><br>';
    echo '<select name="job_company"><option value="">（選択してください）</option>';
    foreach ($companies as $c) {
      printf('<option value="%d"%s>%s</option>',
        $c->ID, selected($cid, $c->ID, false), esc_html(get_the_title($c)));
    }
    echo '</select></p>';

    echo '<p><strong>事業所（複数選択可）</strong><br>';
    echo '<select name="job_offices[]" multiple size="8" style="min-width:320px;">';
    foreach ($offices as $o) {
      $label = get_the_title($o);
      $p = get_post_meta($o->ID, 'office_prefecture', true);
      $c = get_post_meta($o->ID, 'office_city', true);
      if ($p || $c) $label .= '（'.$p.$c.'）';
      printf('<option value="%d"%s>%s</option>',
        $o->ID, selected(in_array($o->ID, $office_ids), true, false), esc_html($label));
    }
    echo '</select><br><small>※ Ctrl/⌘ キーで複数選択</small></p>';
  }, 'job', 'normal');
});

// ============== 保存 ==============
add_action('save_post_company', function($post_id){
  if (!isset($_POST['company_fields_nonce']) || !wp_verify_nonce($_POST['company_fields_nonce'],'save_company_fields')) return;
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!current_user_can('edit_post', $post_id)) return;
  if (isset($_POST['company_id_src'])) update_post_meta($post_id,'company_id_src', sanitize_text_field($_POST['company_id_src']));
});

add_action('save_post_office', function($post_id){
  if (!isset($_POST['office_fields_nonce']) || !wp_verify_nonce($_POST['office_fields_nonce'],'save_office_fields')) return;
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!current_user_can('edit_post', $post_id)) return;

  $map = [
    'office_id_src'    => 'text',
    'office_prefecture'=> 'text',
    'office_city'      => 'text',
    'office_desc'      => 'textarea',
  ];
  foreach($map as $key=>$type){
    if (isset($_POST[$key])) {
      $val = ($type==='textarea') ? wp_kses_post($_POST[$key]) : sanitize_text_field($_POST[$key]);
      update_post_meta($post_id, $key, $val);
    }
  }
  // 親企業
  $parent_company = isset($_POST['office_parent_company']) ? (int) $_POST['office_parent_company'] : 0;
  update_post_meta($post_id, 'office_parent_company', $parent_company);

  // ===== 一意なスラッグを自動設定（会社ID×拠点ID 優先） =====
  $office_src  = get_post_meta($post_id, 'office_id_src', true);
  $company_src = $parent_company ? get_post_meta($parent_company, 'company_id_src', true) : '';
  $slug_parts  = [];

  if ($company_src)  $slug_parts[] = 'c'.$company_src;
  elseif ($parent_company) $slug_parts[] = 'c'.$parent_company;

  if ($office_src)   $slug_parts[] = 'o'.$office_src;
  else               $slug_parts[] = sanitize_title(get_the_title($post_id));

  $new_slug = implode('-', $slug_parts);
  $current  = get_post_field('post_name', $post_id);

  if ($new_slug && $current !== $new_slug) {
    // 重複を避ける（同スラッグ存在時は -2, -3 …が自動付与される）
    wp_update_post(['ID' => $post_id, 'post_name' => $new_slug]);
  }
});

add_action('save_post_job', function($post_id){
  if (!isset($_POST['job_fields_nonce']) || !wp_verify_nonce($_POST['job_fields_nonce'],'save_job_fields')) return;
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!current_user_can('edit_post', $post_id)) return;

  if (isset($_POST['job_id_src'])) update_post_meta($post_id,'job_id_src', sanitize_text_field($_POST['job_id_src']));
  if (isset($_POST['job_desc']))   update_post_meta($post_id,'job_desc', wp_kses_post($_POST['job_desc']));

  if (isset($_POST['job_company'])) {
    update_post_meta($post_id, 'job_company', (int) $_POST['job_company']);
  } else {
    delete_post_meta($post_id, 'job_company');
  }
  if (isset($_POST['job_offices']) && is_array($_POST['job_offices'])) {
    $ids = array_map('intval', $_POST['job_offices']);
    update_post_meta($post_id, 'job_offices', $ids);
  } else {
    delete_post_meta($post_id, 'job_offices');
  }
});

// ============== テンプレ用ヘルパー ==============
function get_job_company($job_id) {
  $cid = (int) get_post_meta($job_id, 'job_company', true);
  return $cid ? get_post($cid) : null;
}
function get_job_offices($job_id) {
  $ids = (array) get_post_meta($job_id, 'job_offices', true);
  if (!$ids) return [];
  $ids = array_map('intval', $ids);
  $posts = get_posts([
    'post_type' => 'office',
    'post__in' => $ids,
    'orderby' => 'post__in',
    'numberposts' => -1,
    'post_status' => ['publish','draft','private'],
  ]);
  return $posts;
}


// 事業所カスタム投稿ダッシュボード 絞り込みドロップダウン
add_action('restrict_manage_posts', function(){
  global $typenow;
  if ($typenow !== 'office') return;

  $selected = isset($_GET['filter_company']) ? (int) $_GET['filter_company'] : 0;
  $companies = get_posts([
    'post_type'   => 'company',
    'numberposts' => -1,
    'orderby'     => 'title',
    'order'       => 'ASC',
    'post_status' => ['publish','draft','private'],
  ]);
  echo '<select name="filter_company"><option value="">親企業で絞り込み</option>';
  foreach ($companies as $c) {
    printf('<option value="%d"%s>%s</option>', $c->ID, selected($selected, $c->ID, false), esc_html(get_the_title($c)));
  }
  echo '</select>';
});

// 絞り込みクエリ適用
add_action('pre_get_posts', function($q){
  if (!is_admin() || !$q->is_main_query()) return;
  if ($q->get('post_type') !== 'office') return;

  if (!empty($_GET['filter_company'])) {
    $q->set('meta_query', [
      [
        'key'     => 'office_parent_company',
        'value'   => (int) $_GET['filter_company'],
        'compare' => '='
      ]
    ]);
  }
});

add_action('pre_get_posts', function ($q) {
  if (!is_admin() || !$q->is_main_query()) return;
  if ($q->get('post_type') !== 'job') return;

  if (!empty($_GET['filter_job_company'])) {
    $q->set('meta_query', [[
      'key'     => 'job_company',
      'value'   => (int) $_GET['filter_job_company'],
      'compare' => '=',
    ]]);
  }
});


// 企業名カラム
add_filter('manage_office_posts_columns', function($cols){
  $cols['parent_company'] = '親企業';
  return $cols;
});
add_action('manage_office_posts_custom_column', function($col, $post_id){
  if ($col === 'parent_company') {
    $cid = (int) get_post_meta($post_id, 'office_parent_company', true);
    echo $cid ? esc_html(get_the_title($cid)) : '—';
  }
}, 10, 2);

// 一覧カラム追加
add_filter('manage_job_posts_columns', function ($cols) {
  $cols['job_company_col'] = '企業';
  $cols['job_offices_col'] = '事業所';
  return $cols;
});

// 一覧カラム表示
add_action('manage_job_posts_custom_column', function ($col, $post_id) {
  if ($col === 'job_company_col') {
    $cid = (int) get_post_meta($post_id, 'job_company', true);
    echo $cid ? esc_html(get_the_title($cid)) : '—';
  }
  if ($col === 'job_offices_col') {
    $ids = (array) get_post_meta($post_id, 'job_offices', true);
    if (!$ids) { echo '—'; return; }
    $ids = array_map('intval', $ids);
    $offices = get_posts([
      'post_type'   => 'office',
      'post__in'    => $ids,
      'orderby'     => 'post__in',
      'numberposts' => -1,
      'post_status' => ['publish','draft','private'],
    ]);
    if (!$offices) { echo '—'; return; }

    $labels = [];
    foreach ($offices as $o) {
      $label = get_the_title($o);
      $p = get_post_meta($o->ID, 'office_prefecture', true);
      $c = get_post_meta($o->ID, 'office_city', true);
      if ($p || $c) $label .= '（' . $p . $c . '）';
      $labels[] = $label;
    }
    echo esc_html(implode('、', $labels));
  }
}, 10, 2);


// 企業カスタム投稿ダッシュボード 絞り込みドロップダウン
add_action('restrict_manage_posts', function () {
  if (get_current_screen()->post_type !== 'job') return;

  $selected_company = isset($_GET['filter_job_company']) ? (int) $_GET['filter_job_company'] : 0;
  $companies = get_posts([
    'post_type'   => 'company',
    'numberposts' => -1,
    'orderby'     => 'title',
    'order'       => 'ASC',
    'post_status' => ['publish','draft','private'],
  ]);

  echo '<select name="filter_job_company" style="max-width:220px;">';
  echo '<option value="">企業で絞り込み</option>';
  foreach ($companies as $c) {
    printf('<option value="%d"%s>%s</option>',
      $c->ID, selected($selected_company, $c->ID, false), esc_html(get_the_title($c)));
  }
  echo '</select>';
});


