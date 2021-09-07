<gridrow>
	<phpscript>
		$discharged = $view->isdischarged;
		$indrop = $view->indrop;

		$comment = $view->physio_comment;
		$ppd_select = $view->formCheckbox('palliativpflegedienst', $view->palliativpflegedienst, null, array('1'));
		$act = $view->act;
		$old_f_name = $view->old_physiotherapist_firstname;
		$old_l_name = $view->old_physiotherapist_lastname;
	</phpscript>
	<form action="" method="post" name="frmuserpfl" id="frmuserpfl" autocomplete="off" >
		<div id="family_dr_edit" style="padding:0px; margin: 0px;">
			<br />
			<fieldset>
				<label for="physiotherapist" id="lbl_familydoc_id"><? echo $this->translate('physiotherapist') ?></label>
				<input type="text" name="physiotherapist" class="left" id="physiotherapist" value="[[physiotherapist]]" >
				<input name="old_physiotherapist_firstname" id="old_physiotherapist_firstname" value="[[old_f_name]]" type="hidden">
				<input name="old_physiotherapist_lastname" id="old_physiotherapist_lastname" value="[[old_f_name]]" type="hidden">
				<input type="hidden" name="hidd_physioid" id="hidd_physioid" value="[[id]]"  />
				<div class="clearer"></div>
				
				<div id="doctdropdown" style="position: absolute; left: 10px; border: 0px;"></div>
				<label for="first_name" id="lbl_street1"><? echo $this->translate('firstname') ?></label>
				<input type="text" name="first_name" class="left" id="first_name" value="[[first_name]]" />
				<div class="clearer"></div>
				
				<label for="last_name" id="lbl_street1"><? echo $this->translate('lastname') ?></label>
				<input type="text" name="last_name" class="left" id="last_name" value="[[last_name]]" />
				<div class="clearer"></div>
				
				<label for="street1" id="lbl_street1"><? echo $this->translate('address') ?></label>
				<input type="text" name="street1" class="left" id="street1" value="[[street1]]" />
				<div class="clearer"></div>
				
				<label for="zip" id="lbl_zip"><? echo $this->translate('zip') ?></label>
				<input type="text" name="zip" class="left" id="zip" value="[[zip]]" />
				<div class="clearer"></div>
				
				<label for="city" id="lbl_city"><? echo $this->translate('city') ?></label>
				<input type="text" name="city" class="left" id="city" value="[[city]]" />
				<div class="clearer"></div>
				
				<label for="phone_practice" id="lbl_phone_practice"><? echo $this->translate('phone1') ?></label>
				<input type="text" name="phone_practice" class="left" id="phone_practice" value="[[phone_practice]]" />
				<div class="clearer"></div>

				<label for="phone_emergency" id="lbl_phone_practice">Notruf Telefon</label>
				<input type="text" name="phone_emergency" class="left" id="phone_emergency" value="[[phone_emergency]]" />
				<div class="clearer"></div>

				<label for="fax" id="lbl_fax"><? echo $this->translate('fax') ?></label>
				<input type="text" name="fax" class="left" id="fax" value="[[fax]]" />
				<div class="clearer"></div>
				
				<label for="physio_comment" id="lbl_comm"><? echo $this->translate('comment') ?></label>
				<textarea name="physio_comment" id="physio_comment" cols="20" rows="7" class="modalTextarea">[[comment]]</textarea>
				<div class="clearer"></div>

				<label for="updatemain" id="lbl_updatemain"><? echo $this->translate('PPD') ?></label>
				[[ppd_select]]
				<div class="clearer">

				<input type="submit" name="submit" class="button" id="submit_btn_pfl" value="<? echo $this->translate('submit') ?>" /> 

			</fieldset>
		</div>
	</form>
	<script type="text/javascript">
		var indrop = '[[indrop]]';

		if(indrop<1){
			$("#updatemaindiv").show();
		}

	</script>
</gridrow>