<?php $this->layout('layout', ['title'=>'change_password']) ?>

<?php $this->start('main_content') ?>
	<h2>Changez votre mot de passe</h2>

		<form action="" method="post">
			<div class="form-row">
				<label for="password">Mot de passe
					<input type="password" name="password" id="password">			
				</label>
			</div>
			<br />
			<div class="form-row">
				<label for="confirm_password">Confirmé mot de passe
					<input type="password" name="confirm_password" id="confirm_password">
				</label>
				<div class="help-block text-danger"><?php echo $errorConfirm_password; ?></div>
			</div>
			<button type="submit">Envoyer</button>
		</form>
	
<?php $this->stop('main_content') ?>