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
    <?php
    if (have_posts()) : while (have_posts()) : the_post();
    $job_id     = get_the_ID();
    $job_id_src = get_post_meta($job_id, 'job_id_src', true);
    $job_desc   = get_post_meta($job_id, 'job_desc', true);

    $company = get_job_company($job_id);
    $offices = get_job_offices($job_id);
    ?>
    <article <?php post_class('job-detail'); ?>>
      <div class="entry-header">
        <h1 class="entry-title"><?php the_title(); ?></h1>
        <ul class="entry-meta">
          <?php if ($job_id_src): ?><li>求人ID：<?php echo esc_html($job_id_src); ?></li><?php endif; ?>
        </ul>
      </div>

      <section class="job-desc">
        <?php if ($job_desc): ?>
          <div class="job-desc__body"><?php echo wpautop(esc_html($job_desc)); ?></div>
        <?php else: ?>
          <p>（未入力）</p>
        <?php endif; ?>
      </section>

      <?php if ($company): ?>
      <section class="job-company">
        <h2>企業情報（紐づいたカスタム投稿の内容）</h2>
          <?php if ($company_src): ?>
            <dl>
              <dt>企業ID</dt>
              <dd><?php echo esc_html($company_src); ?></dd>
            </dl>
          <?php endif; ?>
          <dl>
            <dt>企業名</dt>
            <dd><?php echo esc_html(get_the_title($company)); ?></dd>
          </dl>
          <?php $company_src = get_post_meta($company->ID, 'company_id_src', true); ?>
      </section>
      <?php endif; ?>

      <?php if (!empty($offices)): ?>
      <section class="job-offices">
        <h2>事業所情報（紐づいたカスタム投稿の内容）</h2>
        <ul class="office-list">
          <?php foreach ($offices as $o): ?>
            <?php
              $o_src = get_post_meta($o->ID, 'office_id_src', true);
              $pref  = get_post_meta($o->ID, 'office_prefecture', true);
              $city  = get_post_meta($o->ID, 'office_city', true);
              $desc  = get_post_meta($o->ID, 'office_desc', true);
            ?>
            <li class="office-item">
              <?php if ($o_src): ?>
                <dl>
                  <dt>拠点ID</dt>
                  <dd><?php echo esc_html($o_src); ?></dd>
                </dl>
              <?php endif; ?>
              <dl>
                <dt>事業所名</dt>
                <dd><?php echo esc_html(get_the_title($o)); ?></dd>
              </dl>
              <dl>
                <dt>都道府県</dt>
                <dd><?php echo $pref ? esc_html($pref) : '（未入力）'; ?></dd>
              </dl>
              <dl>
                <dt>市区町村</dt>
                <dd><?php echo $city ? esc_html($city) : '（未入力）'; ?></dd>
              </dl>
              <dl>
                <dt>説明</dt>
                <dd><?php echo $desc ? wpautop(esc_html($desc)) : '（未入力）'; ?></dd>
              </dl>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
      <?php endif; ?>
    </article>
    <?php
    endwhile; endif;
    ?>
  </main>

</body>

</html>