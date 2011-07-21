<?php // $Id: page.tpl.php,v 1.18.2.1 2009/04/30 00:13:31 goba Exp $?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $language->language ?>" lang="<?php print $language->language ?>" dir="<?php print $language->dir ?>">
	<head>
		<?php print $head ?>
		<title><?php print $head_title ;print $node->path?></title>
		<?php print $styles ?>
		<?php print $scripts ?>
		<!--[if lt IE 7]>
		<?php print phptemplate_get_ie_styles(); ?>
		<![endif]-->
	</head>
	<body <?php if (substr($node->path, 0, 7)=='gallery' OR arg(0)=='gallery') { print " class='gallery' "; } ?>>
	<!-- layout -->
		<div id="header">
			<?php if (isset($primary_links)) : ?>
				<!--
			 	<?php print theme('links', $primary_links, array('class' => 'links primary-links')) ?>
			 	-->
				<?php print theme('nice_menu_primary_links'); ?>
			<?php endif; ?>

			<a href="/"><img src="/sites/all/themes/ethelburgas/images/logo.png" /></a>
			<?php if (substr($node->path, 0, 7)!='gallery') : ?>
				<?php if (arg(0)!='gallery'): ?>
					<a href="/"><img id="eban"src="/sites/all/themes/ethelburgas/images/banner
						<?php if($is_front) {
						echo '0';
						} elseif($node->path=='themes/world-music') {
						echo '7';
						} elseif(substr($node->path, 0, 18)=='narrative-resource') {
						echo 'nr';
						} else {
						echo rand(1,6);
						}
						?>.jpg" />
					</a>

					<?php if (substr($node->path, 0, 18)!='narrative-resource') : ?>
						<a href="/themes/reconciliation-resources"><img src="/sites/all/themes/ethelburgas/images/reconciliation.png" /></a>
					 	<a href="/themes/facilitation-and-dialogue"><img src="/sites/all/themes/ethelburgas/images/facilitation.png" /></a>
						 <a href="/themes/power-of-stories"><img src="/sites/all/themes/ethelburgas/images/stories.png" /></a>
						 <a href="/themes/refusing-violence"><img src="/sites/all/themes/ethelburgas/images/violence.png" /></a>
						 <a href="/themes/multifaith"><img src="/sites/all/themes/ethelburgas/images/multifaith.png" /></a>
						 <a href="/themes/world-music"><img src="/sites/all/themes/ethelburgas/images/music.png" /></a>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>
		</div> 
		<!-- /header -->

		<div id="center"> 
			<?php if($node->content['field_bg'] != null) { ?>style="background-image:<?php print $node->field_background[0]['view']; ?>"<?php } ?>
			<?php if (substr($node->path, 0, 7)!='gallery') : ?>
				<?php if (arg(0)!='gallery'): ?>
					<div id="etside">
						<?php if ($left): ?>
							<?php if ($search_box): ?>
								<div class="block block-theme">
									<?php print $search_box ?>
								</div>
							<?php endif; ?>
							<?php print $left ?>
						<?php endif; ?>
						<?php if (isset($secondary_links)) : ?>
							<?php // print theme('links', $secondary_links, array('id' => 'secnav')) ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<div class="optionscaa right-corner ">
				<div class="left-corner">
					<?php print $breadcrumb; ?>
					<?php if ($mission): print '<div id="mission">'. $mission .'</div>'; endif; ?>
					<?php if ($tabs): print '<div id="tabs-wrapper" class="clear-block">'; endif; ?>
					<?php if ($right): ?>
						<div id="sidebar-right" class="sidebar">
							<?php if (!$left && $search_box): ?><div class="block block-theme"><?php print $search_box ?></div><?php endif; ?>
							<?php print $right ?>
						</div>
					<?php endif; ?>
					<?php if ($title): print '<h2'. ($tabs ? ' class="with-tabs"' : '') .'>';   print $title .'</h2>';endif;  ?>
					<?php if ($tabs): print '<ul class="tabs primary">'. $tabs .'</ul></div>'; endif; ?>
					<?php if ($tabs2): print '<ul class="tabs secondary">'. $tabs2 .'</ul>'; endif; ?>
					<?php if ($show_messages && $messages): print $messages; endif; ?>
					<?php print $help; ?>
				
					<div class="maincontent clear-block">
						<?php print $content ?>
		  				<?php print $undercontent ?>
					</div>
				
					<?php print $feed_icons ?>
				</div>
			</div>
			<div style="clear:both;"></div>
		</div> <!-- /.left-corner, /.right-corner, /#squeeze, /#center -->
	    <div id="footer">
			<div id="footer-message">
				<?php print $footer_message ?> 
			</div>
			<?php print theme('links', $secondary_links, array('class' => 'links secondary-links'))?> 	
		</div>
	<!-- /layout -->
		<?php print $closure ?>
	</body>
</html>
