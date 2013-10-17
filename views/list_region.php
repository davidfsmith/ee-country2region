<?php

    $this->table->set_template($cp_table_template);
    $this->table->set_heading(
        lang('region_name'),
        lang('country_count'),
        '',
        ''
    );

    // (TODO We should look at using channels here as well ?)
    foreach ($regions as $region)
    {
        $this->table->add_row(
            $region['region_name'],
            $region['country_count'],
            '<a href="'.$region['mod_link'].'">'.lang('mod_region').'</a>',
            '<a href="'.$region['del_link'].'">'.lang('del_region').'</a>'
        );
    }

    echo $this->table->generate();

?>