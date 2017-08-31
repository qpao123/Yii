<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use common\widgets\Alert;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <header id="masthead" class="site-header" role="banner">
        <div class="nav-container">
            <nav id="site-navigation" class="main-navigation" role="navigation">
                <div class="container nav-bar">
                    <div class="row">
                        <div class="module left site-title-container">

                            <a href="./">    <img src="<?= Yii::getAlias("@web").'/img/1-1.png' ?>" alt="SemiCode OS" style="width:100px;margin-top:-20px;">  </a>                            </div>
                        <div class="module widget-handle mobile-toggle right visible-sm visible-xs">
                            <i class="fa fa-bars"></i>
                        </div>
                        <div class="module-group right">
                            <div class="module left">
                                <div class="collapse navbar-collapse navbar-ex1-collapse">
                                    <ul id="menu" class="menu"><li id="menu-item-16" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-home current-menu-item page_item page-item-15 current_page_item menu-item-16 active"><a title="Home" href="http://www.semicodeos.com/">首页</a></li>
                                        <li id="menu-item-12" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-12"><a title="Blog" href="http://www.semicodeos.com/blog/">视频</a></li>
                                        <li id="menu-item-20" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-20"><a title="Download" href="http://www.semicodeos.com/download/">直播</a></li>
                                        <li id="menu-item-1499" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-1499"><a title="Screenshots" href="http://www.semicodeos.com/screenshots/">电台</a></li>
                                        <li id="menu-item-26" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-26"><a title="Developer" href="http://www.semicodeos.com/developer/">文章</a></li>
                                        <li id="menu-item-23" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-23"><a title="Get Involved" href="http://www.semicodeos.com/get-involved/">关于我</a></li>
                                    </ul>
                                </div>
                            </div>
                            <!--end of menu module-->
                            <div class="module widget-handle search-widget-handle left hidden-xs hidden-sm">
                                <div class="search">
                                    <i class="fa fa-search"></i>
                                    <span class="title">Site Search</span>
                                </div>
                                <div class="function"><form role="search" method="get" id="searchform" class="search-form" action="http://www.semicodeos.com/">
                                        <label class="screen-reader-text" for="s">Search for:</label>
                                        <input type="text" placeholder="Type Here" value="" name="s" id="s">
                                        <input type="submit" class="btn btn-fillded searchsubmit" id="searchsubmit" value="Search">

                                    </form>                                    </div>
                            </div>
                        </div>
                        <!--end of module group-->
                    </div>
                </div>
            </nav><!-- #site-navigation -->
        </div>
    </header>

    <?= $content ?>
</div>

<footer id="colophon" class="site-footer footer bg-dark" role="contentinfo">
    <div class="container footer-inner">
        <div class="row">
            <div class="footer-widget-area">
                <div class="col-md-3 col-sm-3 footer-widget" role="complementary">
                    <div id="social_media_widget-2" class="widget widget_social_media_widget" style="margin-bottom:20px;">
                        <h2 class="widget-title">Be in Touch</h2>
                        414924927@qq.com
                    </div>
                </div><!-- .widget-area .first -->
            </div>
        </div>

        <div class="row">
            <div class="site-info col-sm-6">
                <div class="copyright-text"></div>
                <div class="footer-credits">Made by <a href="http://www.semicodeos.com/company" target="_blank" title="Colorlib">shawn.zheng</a> Powered by <a href="http://www.semicodeos.com" target="_blank" title="WordPress.org">zheng OS</a></div>
            </div><!-- .site-info -->
            <div class="col-sm-6 text-right">
            </div>
        </div>
    </div>

    <a class="btn btn-sm fade-half back-to-top inner-link" id="back-top">
        <span class="glyphicon glyphicon-chevron-up" aria-hidden="true"></span>
    </a>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
