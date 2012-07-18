<div id="hybridauth-openid-div">
	<p>Enter your OpenID identity or provider:</p>
	<form action="<?php echo $this->config['baseUrl'];?>/default/login/" method="get" id="hybridauth-openid-form" >
		<input type="hidden" name="provider" value="openid"/>
		<input type="text" name="openid-identity" size="30"/>
	</form>
</div>

<?php foreach (Yii::app()->user->getFlashes() as $key => $message): ?>
	<div class="flash-error"> <?php echo $message ?> </div>
<?php endforeach; ?>		

<ul class='hybridauth-providerlist'>
	<?php foreach ($providers as $provider => $settings): ?>
		<?php if($settings['enabled'] == true): ?> 
			<li>
				<a id="hybridauth-<?php echo $provider ?>" href="<?php echo $baseUrl?>/default/login/?provider=<?php echo $provider ?>" >
					<img src="<?php echo $assetsUrl ?>/images/<?php echo strtolower($provider)?>.png"/>
				</a>
			</li>
		<?php endif; ?>
	<?php endforeach; ?>
</ul>