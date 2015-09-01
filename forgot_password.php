<?php $this->layout('layout', ['title'=>'forgot_password']) ?>

<?php $this->start('main_content') ?>
	<h2>Mot de passe oublié</h2>

		<form action="" method="post">
			<div class="form-row">
				<label for="email">Votre email
					<input type="email" name="email" id="email">
					<input type="submit" value="OK">
					<div class="help-block text-danger"><?php echo $errorEmail; ?></div>
				</label>
			</div>

		</form>
	
<?php $this->stop('main_content') ?>