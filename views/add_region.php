<?=form_open($action_url, '', $form_hidden)?>
<h3><?=$message?></h3>
<?php

    $this->table->set_template($cp_table_template);
    $this->table->set_heading(
        lang('region_name')
    );

    // (TODO We should look at using channels here as well ?)
    $this->table->add_row(
        form_input(array(
            'name'      => 'region_name',
            'value'     => $region_name,
            'maxlength' => 250,
            'size'      => '50',
            'style'     => 'width:50%'))." (".lang('region_name_notes').")"
    );

    echo $this->table->generate();

    $this->table->set_template($cp_table_template);
    $this->table->set_heading(
        lang('country_code'),
        lang('country_name'),
        lang('country_flag'),
        form_checkbox('select_all', 'true', false, 'class="toggle_all" id="select_all"')
    );

    foreach($countries as $country)
    {
        $this->table->add_row(
            $country['country_code'],
            $country['country_name'],
            "<img src=\"".$flag_image_path."/flag_".$country['country_code'].".gif\" />",
            form_checkbox($country['country_code'], 'y', $country['status'], "id='country_code_{$country['country_code']}'")
        );
    }

echo $this->table->generate();

?>

<div class="tableFooter">
    <div class="tableSubmit">
        <?=form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit'))?>
    </div>
</div>

<?=form_close()?>
