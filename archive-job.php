<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>
  <!-- favicon -->
  <link rel="icon" type="image/png" href="images/fav.png" />
  <!-- <meta name="robots" content="index, follow"> -->
  <meta name="format-detection" content="telephone=no">
  <!-- css -->
  <link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/css/reset.css" >
  <link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/css/style.css" >
  
  <title>テスト｜ダイバージェンス</title>
  <meta name="description" content="" />
  <meta name="keywords" content="" />
</head>

<body>

  <main id="primary" class="site-main">
    <header class="page-header">
      <h1 class="page-title">求人一覧</h1>
    </header>

    <?php if (have_posts()) : ?>
      <div class="job-archive-list">
        <?php while (have_posts()) : the_post(); ?>
          <article <?php post_class('job-card'); ?>>
            <?php
              $job_id   = get_the_ID();
              $company  = get_job_company($job_id);
              $offices  = get_job_offices($job_id);
              $company_name = $company ? get_the_title($company) : '';
              $head_loc = '';
              if (!empty($offices)) {
                $o0   = $offices[0]; // 最初の拠点
                $pref = get_post_meta($o0->ID, 'office_prefecture', true);
                $city = get_post_meta($o0->ID, 'office_city', true);
                $head_loc = trim(($pref ?: '') . ($city ?: '')); // 「都道府県＋市区町村」
              }
              $head_ttl = '';
              if ($company_name || $head_loc) {
                $head_ttl = ($head_loc ? $head_loc . ' ' : '') . $company_name . 'の採用・求人情報';
              }
              if ($head_ttl) {
                echo '<p class="head-ttl">' . esc_html($head_ttl) . '</p>';
              }
            ?>
            <h2 class="job-card__title">
              <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h2>
            <div class="job-card__meta">
              <?php
                $job_id_src = get_post_meta(get_the_ID(), 'job_id_src', true);
                if ($job_id_src) {
                  echo '<span class="job-card__id">ID: ' . esc_html($job_id_src) . '</span>';
                }
              ?>
            </div>
            <?php
              // 管理で入力した「求人説明文（job_desc）」の冒頭を軽く表示（全文は詳細で）
              $desc = get_post_meta(get_the_ID(), 'job_desc', true);
              if ($desc) {
                echo '<p class="job-card__desc">' . esc_html(mb_strimwidth(wp_strip_all_tags($desc), 0, 120, '…', 'UTF-8')) . '</p>';
              }
            ?>
            <a class="btn" href="<?php the_permalink(); ?>">詳細を見る</a>
          </article>
        <?php endwhile; ?>
      </div>

      <nav class="pagination">
        <?php
          the_posts_pagination([
            'mid_size' => 2,
            'prev_text' => '« 前へ',
            'next_text' => '次へ »',
          ]);
        ?>
      </nav>

    <?php else : ?>
      <p>掲載中の求人はありません。</p>
    <?php endif; ?>
  </main>

</body>

</html>