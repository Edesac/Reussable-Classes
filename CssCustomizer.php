<?php
class CssCustomizerV1 {

    public static $font_weight_options = array('n' => 'Normal', 'b' => 'Bold', 'bo'=> 'Bolder');
    public static $font_style = array('n' => 'Normal', 'i' => 'Italic');
    public static $text_decoration = array('n' => 'None', 'u' => 'Underline');
    public static $border_style = array('n' => 'None', 's' => 'Solid', 'da' => 'dashed', 'do' => 'dotted', 'dou' => 'double');
    public static $vertical_alignment = array('t' => 'Top', 'm' => 'Middle', 'b' => 'Bottom');
    public static $background_size = array('a' => 'Auto', 'c' => 'Cover');   
    public static $background_repeat = array('r' => 'Repeat', 'nr' => 'No Repeat', 'rx' => 'Repeat X', 'ry' => 'Repeat Y');
    public static $background_attachment = array('s' => 'Scroll', 'f' => 'Fixed');

    protected $_rows_css;
    protected $_column_css;
    protected $_blocks_css;   
    
    protected $_cols_css_option_name;
    protected $_blocks_css_option_name;
    protected $_rows_css_option_name;    

    public function __construct($args)
    {
        $this->_column_css = $args['col_css'];
        $this->_rows_css = $args['row_css'];
        $this->_blocks_css = $args['blocks_css'];
        $this->_cols_css_option_name = $args['col_css_field_name'];
        $this->_blocks_css_option_name = $args['block_css_field_name'];
        $this->_rows_css_option_name = $args['row_css_field_name'];
    }

    public static function initAjax()
    {
        add_action('wp_ajax_get-row-css', array('CssCustomizer', 'ajaxRowCss'));
        add_action('wp_ajax_nopriv_get-row-css', array('CssCustomizer', 'ajaxRowCss'));//for users that are not logged in                 

        add_action('wp_ajax_get-col-css', array('CssCustomizer', 'ajaxColCss'));
        add_action('wp_ajax_nopriv_get-col-css', array('CssCustomizer', 'ajaxColCss'));//for users that are not logged in  

        add_action('wp_ajax_get-element-css', array('CssCustomizer', 'ajaxElementCss'));
        add_action('wp_ajax_nopriv_get-element-css', array('CssCustomizer', 'ajaxElementCss'));//for users that are not logged in  
        
        add_action('wp_ajax_get-block-css', array('CssCustomizer', 'ajaxBlockCss'));
        add_action('wp_ajax_nopriv_get-block-css', array('CssCustomizer', 'ajaxBlockCss'));//for users that are not logged in          
        
        add_action('wp_ajax_save-element-css', array('CssCustomizer', 'ajaxSaveElementCss'));
        add_action('wp_ajax_nopriv_save-element-css', array('CssCustomizer', 'ajaxBlockCss'));//for users that are not logged in 
    }

    //<<<<<<<<<<<<<<<<<<<<<<<< AJAX FUNCTIONS <<<<<<<<<<<<<<<<<<<<<<<<<
    public function ajaxSaveElementCss() 
    {
        $user_data = $_POST;
        $final_data = array();
        $guid = $_POST['element-id'];
        $builder_id = $_POST['builder-id'];
        $args['builder-url'] = ORIGO_SLIDER_ADMIN_URL . 'includes/html-builder-v1';
        
        $db_builder = new DB_Table_Elements();
        $user_input['css'] = serialize($_POST['b']['elements'][$guid]);
        $user_input['builder-id'] = $builder_id;
        $user_input['guid'] = $guid;
        $user_input['status'] = 1;
        
        $db_builder->save2($user_input, 'guid = "' . $guid . '"');        

        print_r(json_encode($user_data));
        exit;
    }
    
    public static function ajaxRowCss()
    {
        global $HTMLBuilderV1_URL;
        $row_id = $_POST['row-id'];        
        $builder = new DatabaseTable_TemplateBuilderV1();
        $builder_id = $_POST['builder-id'];
        $data = $builder->getByField('builder_id', $_POST['builder-id']);

        $args2 = array(
                    'row_css' => unserialize($data[0]->css_rows),
                    'col_css' => unserialize($data[0]->css_columns),
                    'blocks_css' => unserialize($data[0]->css_blocks),
                    'row_css_field_name' => HtmlBuilderV1::getRowsCssName($builder_id),
                    'col_css_field_name' => HtmlBuilderV1::getColumnsCssName($builder_id),
                    'block_css_field_name' => HtmlBuilderV1::getBlocksCssName($builder_id),
                );        
        $page_builder = new CssCustomizer($args2);      
        
        //$this->_column_css = unserialize($data[0]->css_columns);
        //$this->_rows_css = unserialize($data[0]->css_rows);
        //$this->_blocks_css = unserialize($data[0]->css_blocks);

        ob_start();
        $page_builder->displayRowCss($row_id, $page_builder);
        $html = ob_get_contents();
        ob_end_clean();
        $data['html'] = $html;
        $data['row_id'] = $row_id;
        $data['selector'] = $_POST['selector'];

        print_r(json_encode($data));
        exit;
    }
    
    public static function ajaxElementCss()
    {
        global $HTMLBuilderV1_URL;
        $element_id = $_POST['element-id'];
        $col_order = $_POST['col-order'];
        
        $builder_id = $_POST['builder-id'];
        $builder = new DB_Table_Elements($builder_id);
        $data = $builder->getByField('guid', $element_id);
        $css = unserialize($data[0]->css);
        $args2 = array(
                    'row_css' => unserialize($data[0]->css_rows),
                    'col_css' => unserialize($data[0]->css_columns),
                    'blocks_css' => unserialize($data[0]->css_blocks),
                    'row_css_field_name' => HtmlBuilderV1::getRowsCssName($builder_id),
                    'col_css_field_name' => HtmlBuilderV1::getColumnsCssName($builder_id),
                    'block_css_field_name' => HtmlBuilderV1::getBlocksCssName($builder_id),
                );
        
        $page_builder = new CssCustomizerV1($args2);
        $type = HtmlBuilderFrontV2::getType($element_id);
        ob_start();
        $field_prefix = 'b[elements][' . $element_id . ']';
        
        if ($type == 'con') {
            echo $page_builder->containerCSSPopUp($field_prefix, $css, $element_id);
        }
        else {
            echo $page_builder->elementCSSPopUp($field_prefix, $css, $element_id, $row_id, $col_order);
        }
        
        $html = ob_get_contents();
        ob_end_clean();
        
        $data['html'] = $html;
        $data['row_id'] = $row_id;
        $data['selector'] = $_POST['selector'];

        print_r(json_encode($data));
        exit;
    }
    
    public static function ajaxColCss()
    {
        global $HTMLBuilderV1_URL;
        $row_id = $_POST['row-id'];        
        $col_order = $_POST['col-order']; 
        $builder = new DatabaseTable_TemplateBuilderV1();
        $builder_id = $_POST['builder-id'];
        $data = $builder->getByField('builder_id', $_POST['builder-id']);

        $args2 = array(
                    'row_css' => unserialize($data[0]->css_rows),
                    'col_css' => unserialize($data[0]->css_columns),
                    'blocks_css' => unserialize($data[0]->css_blocks),
                    'row_css_field_name' => HtmlBuilderV1::getRowsCssName($builder_id),
                    'col_css_field_name' => HtmlBuilderV1::getColumnsCssName($builder_id),
                    'block_css_field_name' => HtmlBuilderV1::getBlocksCssName($builder_id),
                );        
        $page_builder = new CssCustomizerV1($args2);      
        
        //$this->_column_css = unserialize($data[0]->css_columns);
        //$this->_rows_css = unserialize($data[0]->css_rows);
        //$this->_blocks_css = unserialize($data[0]->css_blocks);

        ob_start();
        $field_prefix = 'b[' . $page_builder->_cols_css_option_name . '][' . $row_id . '][' . $col_order . ']';
        echo $page_builder->cssEditorTabsPopUp($field_prefix, $page_builder->_column_css[$row_id][$col_order], $row_id, $col_order, 'column');
        $html = ob_get_contents();
        ob_end_clean();
        
        $data['html'] = $html;
        $data['row_id'] = $row_id;
        $data['selector'] = $_POST['selector'];

        print_r(json_encode($data));
        exit;
    }    
    
    public function ajaxBlockCss()
    {
        global $HTMLBuilderV1_URL;
        $row_id = $_POST['row-id'];        
        $col_order = $_POST['col-order']; 
        $block_id = $_POST['block-id'];  
        $builder = new DatabaseTable_TemplateBuilderV1();
        $builder_id = $_POST['builder-id'];
        $data = $builder->getByField('builder_id', $_POST['builder-id']);

        $args2 = array(
                    'row_css' => unserialize($data[0]->css_rows),
                    'col_css' => unserialize($data[0]->css_columns),
                    'blocks_css' => unserialize($data[0]->css_blocks),
                    'row_css_field_name' => HtmlBuilderV1::getRowsCssName($builder_id),
                    'col_css_field_name' => HtmlBuilderV1::getColumnsCssName($builder_id),
                    'block_css_field_name' => HtmlBuilderV1::getBlocksCssName($builder_id),
                );        
        $page_builder = new CssCustomizerV1($args2);      
        
        //$this->_column_css = unserialize($data[0]->css_columns);
        //$this->_rows_css = unserialize($data[0]->css_rows);
        //$this->_blocks_css = unserialize($data[0]->css_blocks);

        ob_start();
        $field_prefix = 'b[' . $page_builder->_blocks_css_option_name . '][' . $block_id . ']';
        echo $page_builder->cssEditorTabsPopUp($field_prefix, $page_builder->_blocks_css[$block_id], $row_id, $col_order, $block_id, 'block');    
        $html = ob_get_contents();
        ob_end_clean();
        
        $data['html'] = $html;
        $data['row_id'] = $row_id;
        $data['col_order'] = $col_order;
        $data['block_id'] = $block_id;
        $data['selector'] = $_POST['selector'];

        print_r(json_encode($data));
        exit;
    }     
    //>>>>>>>>>>>>>>>>>>>>>>>> AJAX FUNCTIONS >>>>>>>>>>>>>>>>>>>>>>>>>        

    //<<<<<<<<<<<<<<<<<<<<<<<<     GETTERS    >>>>>>>>>>>>>>>>>>>>>>>>>
    public function getCssColumns()
    {
        return $this->_column_css;
    }    
    
    public function getCssBlocks()
    {
        return $this->_blocks_css;
    }    
    
    public function getCssRows()
    {
        return $this->_rows_css;
    }    
            
    public function getCssRowFieldName()
    {
        return $this->_rows_css_option_name;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>    GETTERS    >>>>>>>>>>>>>>>>>>>>>>>>>
    public function containerCSSPopUp($field_prefix, $values, $element_id)
    {
        $idx = $row_id . '-' . $col_id;
        if (!is_array($values)) {
            $values = array();
        }
        
        ob_start();    
    ?>
    <div class="element-css-editor">
        <div class="element-css-editor-inner">
            <div class="element-css-editor-inner2">       
                <div class="css-save-buttons">
                <?php
                $save_col_id = $col_id;
                $save_row_id = $row_id;
                $type = HtmlBuilderFrontV2::getType($element_id);
                ?>                    
                <a href="#" class="button-primary save-element-css" 
                   data-row-id="<?php echo $save_row_id ?>" 
                   data-col-id="<?php echo $save_col_id ?>"  
                   data-element-id="<?php echo $element_id ?>"  
                   data-prefix="<?php echo $field_prefix ?>">Save Styles</a>
                <a href="#" class="button-primary save-element-css-done" 
                   data-row-id="<?php echo $save_row_id ?>" 
                   data-col-id="<?php echo $save_col_id ?>" 
                   data-element-id="<?php echo $element_id ?>"  
                   data-prefix="<?php echo $field_prefix ?>">Save Styles and Close</a>
                </div>                
                <div class="cose-tabs">
                    <ul>
                        <li><a href="#element-css-general">General</a></li>
                    </ul>
                    <div id="element-css-general">                     
                        <?php CssCustomizerV1::getContainerCss($type, $field_prefix, $values[$type], $idx) ?>
                    </div>
                </div>                                                                                   
                <br />


            </div>
        </div>
    </div>  
    <?php
        $html = ob_get_contents();
        ob_clean();
        return $html;
    }
    
    public function elementCSSPopUp($field_prefix, $values, $element_id, $row_id, $col_id)
    {
        $idx = $row_id . '-' . $col_id;
        if (!is_array($values)) {
            $values = array();
        }
        
        ob_start();    
    ?>
    <div class="element-css-editor">
        <div class="element-css-editor-inner">
            <div class="element-css-editor-inner2">       
                <div class="css-save-buttons">
                <?php
                $save_col_id = $col_id;
                $save_row_id = $row_id;
                $type = HtmlBuilderFrontV2::getType($element_id);
                ?>                    
                <a href="#" class="button-primary save-element-css" 
                   data-row-id="<?php echo $save_row_id ?>" 
                   data-col-id="<?php echo $save_col_id ?>"  
                   data-element-id="<?php echo $element_id ?>"  
                   data-prefix="<?php echo $field_prefix ?>">Save Styles</a>
                <a href="#" class="button-primary save-element-css-done" 
                   data-row-id="<?php echo $save_row_id ?>" 
                   data-col-id="<?php echo $save_col_id ?>" 
                   data-element-id="<?php echo $element_id ?>"  
                   data-prefix="<?php echo $field_prefix ?>">Save Styles and Close</a>
                </div>                
                <div class="cose-tabs">
                    <ul>
                        <li><a href="#element-css-general">General</a></li>
                    </ul>
                    <div id="element-css-general">                     
                        <?php CssCustomizerV1::getTyporaphyCss($type, $field_prefix, $values[$type], $idx) ?>
                    </div>
                </div>                                                                                   
                <br />


            </div>
        </div>
    </div>  
    <?php
        $html = ob_get_contents();
        ob_clean();
        return $html;
    }
    
    /**
     * this displays the html markup for showing css options - pop up window
     * @param type $field_prefix - the prefix for the field name
     * @param array $values - css values
     * @param type $row_id - row id
     * @param type $col_id - column id
     * @return type - text (html markup)
     */
    public function cssEditorTabsPopUp($field_prefix, $values, $row_id, $col_id, $block_id = '', $type = 'column')
    {
        $idx = $row_id . '-' . $col_id;
        if (!is_array($values)) {
            $values = array();
        }
        
        ob_start();    
    ?>
    <div class="css-editor-col">
        <div class="css-editor-col-inner">
            <div class="css-editor-col-inner2">        
                <?php
                $save_col_prefix = $field_prefix;
                $save_col_id = $col_id;
                $save_row_id = $row_id;
                
                if ($type == 'block') {
                    $action2 = 'save-block';
                }
                else {
                    $action2 = 'save-col';
                }
                ?>
                <a href="#" class="button-primary <?php echo $action2 ?>" 
                   data-row-id="<?php echo $save_row_id ?>" 
                   data-col-id="<?php echo $save_col_id ?>"  
                   data-block-id="<?php echo $block_id ?>"  
                   data-col-prefix="<?php echo $save_col_prefix ?>">Save Styles</a>
                <a href="#" class="button-primary <?php echo $action2 ?>-done" 
                   data-row-id="<?php echo $save_row_id ?>" 
                   data-col-id="<?php echo $save_col_id ?>" 
                   data-block-id="<?php echo $block_id ?>"  
                   data-col-prefix="<?php echo $save_col_prefix ?>">Save Styles and Close</a>                
                <div class="cose-tabs">
                    <ul>
                        <li><a href="#my-wordpress-tabs-1x">General</a></li>
                        <li><a href="#my-wordpress-tabs-2x">Par</a></li>
                        <li><a href="#my-wordpress-tabs-3x">H1</a></li>
                        <li><a href="#my-wordpress-tabs-4x">H2</a></li>
                        <li><a href="#my-wordpress-tabs-5x">H3</a></li>
                        <li><a href="#my-wordpress-tabs-6x">H4-6</a></li>
                        <li><a href="#my-wordpress-tabs-7x">Link</a></li>
                        <li><a href="#my-wordpress-tabs-8x">List</a></li>
                    </ul>
                    <div id="my-wordpress-tabs-1x">
                        <h3>Container</h3>
                        <div>
                            <label>Vertical Alignment:</label> 
                            <?php echo CssCustomizerV1::showDropDownOptions($field_prefix . '[con][va]', 
                                    CssCustomizerV1::$vertical_alignment, $values['con']['va']) ?>
                        </div>                        
                        <?php                         
                        CssCustomizerV1::getContainerCss('con', $field_prefix, $values['con'], $idx) ?>
                        <?php CssCustomizerV1::getTyporaphyCss('con', $field_prefix, $values['con'], $idx);
                        ?>                        
                    </div>
                    <div id="my-wordpress-tabs-2x">
                        <h3>Paragraph</h3>                        
                        <?php CssCustomizerV1::getTyporaphyCss('p', $field_prefix, $values['p'], $idx) ?>
                        <?php CssCustomizerV1::getContainerCss('p', $field_prefix, $values['p'], $idx) ?>
                    </div>
                    <div id="my-wordpress-tabs-3x">
                        <h3>H1</h3>
                        <?php CssCustomizerV1::getTyporaphyCss('h1', $field_prefix, $values['h1'], $idx) ?>
                        <?php CssCustomizerV1::getContainerCss('h1', $field_prefix, $values['h1'], $idx) ?>
                    </div>                      
                    <div id="my-wordpress-tabs-4x">
                        <h3>H2</h3>
                        <?php CssCustomizerV1::getTyporaphyCss('h2', $field_prefix, $values['h2'], $idx) ?>
                        <?php CssCustomizerV1::getContainerCss('h2', $field_prefix, $values['h2'], $idx) ?>
                    </div>                            
                    <div id="my-wordpress-tabs-5x">                         
                        <h3>H3</h3>
                        <?php CssCustomizerV1::getTyporaphyCss('h3', $field_prefix, $values['h3'], $idx) ?>
                        <?php CssCustomizerV1::getContainerCss('h3', $field_prefix, $values['h3'], $idx) ?>
                    </div>                      
                    <div id="my-wordpress-tabs-6x">
                        <h3>H4, H5, H6</h3>
                        <?php CssCustomizerV1::getTyporaphyCss('h4', $field_prefix, $values['h4'], $idx) ?>
                        <?php CssCustomizerV1::getContainerCss('h4', $field_prefix, $values['h4'], $idx) ?>
                    </div>      
                    <div id="my-wordpress-tabs-7x">
                        <div class="cose-tabs">
                            <ul>
                                <li><a href="#link-tab">Link</a></li>
                                <li><a href="#link-hover-tab">Link Hover</a></li>
                            </ul>
                            <div id="link-tab">
                                <?php //CssCustomizerV1::getLinkCss('a', $field_prefix, $values['a'], $idx) ?>
                                <?php CssCustomizerV1::getTyporaphyCss('a', $field_prefix, $values['a'], $idx) ?>
                                <?php CssCustomizerV1::getContainerCss('a', $field_prefix, $values['a'], $idx) ?>
                            </div>
                            <div id="link-hover-tab">
                                <?php //CssCustomizerV1::getLinkHoverCss('a-colon-hover', $field_prefix, $values['a-colon-hover'], $idx) ?>
                                <?php CssCustomizerV1::getTyporaphyCss('a-colon-hover', $field_prefix, $values['a-colon-hover'], $idx) ?>
                                <?php CssCustomizerV1::getContainerCss('a-colon-hover', $field_prefix, $values['a-colon-hover'], $idx) ?>
                            </div>                            
                        </div>                            
                    </div>                                 
                    <div id="my-wordpress-tabs-8x">
                        <?php CssCustomizerV1::getListCss('ul', $field_prefix, $values['ul'], $idx) ?>                                
                    </div>
                </div>                                                                                   
                <br />

            </div>
        </div>
    </div>  
    <?php
        $html = ob_get_contents();
        ob_clean();
        return $html;
    }
    
    public function displayRowCss($idx, $theme_builder_css)
    {        
        $css_row_tmp = $theme_builder_css->getCssRows();
        $css_row = $css_row_tmp[$idx];
        
?>
            <div class="css-editor-row" id="css-<?php echo $idx ?>">
                <div class="inner-css-editor-row">
                    <div class="inner2-css-editor-row">
                        <div class="cose-tabs">
                            <ul>
                                <li><a href="#tab-outer-row">Outer Row</a></li>
                                <li><a href="#tab-inner-row">Inner Row</a></li>
<!--                                <li><a href="#tab-column">Column</a></li>-->
                                <li><a href="#my-wordpress-tabs-2x">Par</a></li>
                                <li><a href="#my-wordpress-tabs-3x">H1</a></li>
                                <li><a href="#my-wordpress-tabs-4x">H2</a></li>
                                <li><a href="#my-wordpress-tabs-5x">H3</a></li>
                                <li><a href="#my-wordpress-tabs-6x">H4-6</a></li>
                                <li><a href="#my-wordpress-tabs-7x">Link</a></li>
                                <li><a href="#my-wordpress-tabs-8x">List</a></li>
                            </ul>
<!--                            <div id="tab-column">
                                <?php
                                    CssCustomizerV1::getContainerCss('con', 'b[' . $theme_builder_css->getCssRowFieldName() . '][' . $idx . '][inner]', 
                                        $css_row['inner']['con'], $idx);
                                    ?>
                                <?php 
                                    CssCustomizerV1::getTyporaphyCss(
                                        'div-col', 
                                        'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner]', 
                                        $css_row['inner']['div-col'], $idx
                                    );
                                ?>                                                            
                            </div>-->
                            <div id="tab-outer-row">
                                <h3>Outer Row</h3>
                                <div class="form-row">
                                    <label>Margin:</label>
                                    <?php 
                                    echo $this->getMeasurementMarginSliderField(
                                            'b[' . $this->_rows_css_option_name . '][' . $idx . '][outer][m-', 
                                            array('top' => $css_row['outer']['m-pt'],
                                                  'right' => $css_row['outer']['m-pr'],
                                                  'bottom' => $css_row['outer']['m-pb'],
                                                  'left' => $css_row['outer']['m-pl'],
                                                  'mt' => $css_row['outer']['m-mt'],
                                                ),
                                            'outer-m-' . $idx );
                                    ?>
                                </div>    
                                <div class="form-row">
                                    <label>Padding:</label> 
                                    <?php 
                                        echo $this->getMeasurementPaddingSliderField(
                                            'b[' . $this->_rows_css_option_name . '][' . $idx . '][outer][pa-', 
                                            array('top' => $css_row['outer']['pa-pt'],
                                                  'right' => $css_row['outer']['pa-pr'],
                                                  'bottom' => $css_row['outer']['pa-pb'],
                                                  'left' => $css_row['outer']['pa-pl'],
                                                  'mt' => $css_row['outer']['pa-mt'],
                                                ),
                                            'outer-pa-' . $idx );
                                    ?>                          
                                </div>    
                                <div class="form-row">
                                    <label>Background</label>
                                    <div class="uploader">
                                        <input id="slide-image-<?php echo $idx ?>-outer" 
                                               name="b[<?php echo $this->_rows_css_option_name ?>][<?php echo $idx ?>][outer][bi]" 
                                               type="text"  value="<?php echo $css_row['outer']['bi'] ?>" />
                                        <input class="button media-uploader" rel="slide-image-<?php echo $idx ?>-outer"type="button" value="Upload" />
                                    </div>
                                    <div class="color-picker2">
                                        <label style="display: none" class="field-label">Color</label>
                                        <input name="b[<?php echo $this->_rows_css_option_name ?>][<?php echo $idx ?>][outer][bc]"
                                               class="wp-color-picker2"  value="<?php echo $css_row['outer']['bc'] ?>" />
                                    </div>                                          
                                </div>
                                <div class="form-row">
                                    <label><span style="color: #aaa; font-size: 12px;">-</span></label>
                                    <?php echo $this->showBgSizeOptions('b[' . $this->_rows_css_option_name . '][' . $idx . '][outer][bs', $css_row['outer']['bs1'], $css_row['outer']['bs2']) ?>
                                    <?php echo $this->showBgRepeatOptions('b[' . $this->_rows_css_option_name . '][' . $idx . '][outer][br]', $css_row['outer']['br']) ?>
                                    <?php echo CssCustomizerV1::showBgPositionOptions('b[' . $this->_rows_css_option_name . '][' . $idx . '][outer][bp', $css_row['outer']['bp1'], $css_row['outer']['bp2']) ?>
                                    <?php echo CssCustomizerV1::showDropDownOptions('b[' . $this->_rows_css_option_name . '][' . $idx . '][outer][ba]', CssCustomizerV1::$background_attachment, $css_row['outer']['ba']) ?>                                    
                                </div>                                  
                                <div class="form-row">
                                    <label>Gradient Background</label>
                                    <em>Pro</em>
                                </div>

                                <div class="form-row">
                                    <label>Border Color:</label> 
                                    <div class="color-picker2">
                                        <label style="display: none" class="field-label">Color</label>
                                        <input name="b[<?php echo $this->_rows_css_option_name ?>][<?php echo $idx ?>][outer][boc]" 
                                               class="wp-color-picker2" value="<?php echo $css_row['outer']['boc'] ?>" />
                                    </div>                              
                                </div>            
                                <div class="form-row">
                                    <label>Border Widths:</label>      
                                    <?php 
                                        echo $this->getMeasurementBorderRadius(
                                            'b[' . $this->_rows_css_option_name . '][' . $idx . '][outer][bow-', 
                                            array('top' => $css_row['outer']['bow-pt'],
                                                  'right' => $css_row['outer']['bow-pr'],
                                                  'bottom' => $css_row['outer']['bow-pb'],
                                                  'left' => $css_row['outer']['bow-pl'],
                                                  'mt' => $css_row['outer']['bow-mt'],
                                                ),
                                            'innerbow-' . $idx);
                                    ?>
                                </div>
                                <div class="form-row">
                                    <label>Border Style:</label>
                                    <?php
                                        echo $this->showDropDownOptions(
                                            'b[' . $this->_rows_css_option_name . '][' . $idx . '][outer][bos]', 
                                            CssCustomizerV1::$border_style, 
                                            $css_row['outer']['bos']);
                                    ?>
                                </div>    
                            </div>
                            <div id="tab-inner-row">
                                <div class="form-row">
                                    <label>Width:</label> 
                                    <?php 
                                        echo CssCustomizerV1::getMeasurementSimple(
                                            'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner][w', 
                                            array('value' => $css_row['inner']['w'], 'mt' => $css_row['inner']['w-mt']), 
                                            'w' . $idx, 0, 100 );
                                    ?>  
                                    
                                    <label>Height:</label> 
                                    <?php 
                                        echo CssCustomizerV1::getMeasurementSimple(
                                            'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner][h', 
                                            array('value' => $css_row['inner']['h'], 'mt' => $css_row['inner']['h-mt']), 
                                            'h' . $idx, 0, 100 );
                                    ?>                                      
                                </div>                        
                                <div class="form-row">
                                    <label>Background</label>
                                    <div class="uploader">
                                        <input id="slide-image-<?php echo $idx ?>-inner" name="b[<?php echo $this->_rows_css_option_name ?>][<?php echo $idx ?>][inner][bi]"
                                               type="text"  value="<?php echo $css_row['inner']['bi'] ?>" />
                                        <input class="button media-uploader" rel="slide-image-<?php echo $idx ?>-inner" type="button" value="Upload" />
                                    </div>
                                    <div class="color-picker2">
                                        <label style="display: none" class="field-label">Color</label>
                                        <input name="b[<?php echo $this->_rows_css_option_name ?>][<?php echo $idx ?>][inner][bc]" 
                                               class="wp-color-picker2" value="<?php echo $css_row['inner']['bc'] ?>" />
                                    </div>                                          
                                </div>                        
                                <div class="form-row">
                                    <label><span style="color: #aaa; font-size: 12px;">-</span></label>
                                    <?php 
                                        echo $this->showBgSizeOptions('b[' . $this->_rows_css_option_name . '][' . $idx . '][inner][bs', 
                                            $css['slide']['inner']['bs1'], $css['slide']['inner']['bs2']); 
                                    ?>
                                    <?php 
                                        echo $this->showBgRepeatOptions('b[' . $this->_rows_css_option_name . '][' . $idx . '][inner][br]', 
                                            $css['slide']['inner']['br']); 
                                    ?>
                                </div>     
                                <div class="form-row">
                                    <label>Gradient Background</label>
                                    <em>Pro</em>
                                </div>                        
                                <div class="form-row">
                                    <label>Background Opacity:</label>
                                    <?php echo $this->getDecimalSliderField(
                                            'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner][o]', 
                                            $css_row['inner']['o'], 
                                            'inner-opacity-' . $idx, 0, 10 );                                                          
                                    ?>
                                </div>    
                                
                                <div>
                                    <label>Layer ***:</label> 
                                    <input type="text" name="b[<?php echo $this->_rows_css_option_name ?>][<?php echo $idx ?>][inner][zi]" 
                                        value="<?php echo $css_row['inner']['zi'] ?>" /><em style="font-size: 10px;"><strong>decimal value 0-999999</strong> useful to make drop down menu always on top</em>
                                </div>         

                                <div class="form-row">
                                    <label>Border Color:</label> 
                                    <div class="color-picker2">
                                        <label style="display: none" class="field-label">Color</label>
                                        <input name="b[<?php echo $this->_rows_css_option_name ?>][<?php echo $idx ?>][inner][boc]" 
                                               class="wp-color-picker2" value="<?php echo $css_row['inner']['boc'] ?>" />
                                    </div>                              
                                </div>            
                                <div class="form-row">
                                    <label>Border Width:</label>      
                                    <?php echo $this->getMeasurementBorderRadius(
                                            'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner][bow-', 
                                            array('top' => $css_row['inner']['bow-pt'],
                                                  'right' => $css_row['inner']['bow-pr'],
                                                  'bottom' => $css_row['inner']['bow-pb'],
                                                  'left' => $css_row['inner']['bow-pl'],
                                                  'mt' => $css_row['inner']['bow-mt'],
                                                ),
                                            'innerbow-' . $idx );
                                    ?>                               
                                </div>                
                                <div class="form-row">
                                    <label>Border Style:</label>
                                    <?php echo $this->showDropDownOptions(
                                            'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner][bos]', 
                                            CssCustomizerV1::$border_style, 
                                            $css_row['inner']['bos']);
                                    ?>
                                </div>          
                                <div class="form-row">
                                    <label>Border Radius:</label>     
                                    <?php echo $this->getMeasurementBorderRadius(
                                            'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner][bor-', 
                                            array('top' => $css_row['inner']['bor-top-left-radius'],
                                                  'right' => $css_row['inner']['bor-top-right-radius'],
                                                  'bottom' => $css_row['inner']['bor-bottom-left-radius'],
                                                  'left' => $css_row['inner']['bor-bottom-left-radius'],
                                                  'mt' => $css_row['inner']['bor-radius-mt'],
                                                ),
                                            'outer-padding-' . $idx );
                                    ?>                             
                                </div>
                                <div class="form-row">
                                    <label>Padding:</label> 
                                    <?php echo $this->getMeasurementPaddingSliderField(
                                            'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner][pa-', 
                                            array('top' => $css_row['inner']['pa-pt'],
                                                  'right' => $css_row['inner']['pa-pr'],
                                                  'bottom' => $css_row['inner']['pa-pb'],
                                                  'left' => $css_row['inner']['pa-pl'],
                                                  'mt' => $css_row['inner']['pa-mt'],
                                                ),
                                            'inner-pa-' . $idx );
                                    ?>  
                                </div>                                 
                            </div>
                            <div id="my-wordpress-tabs-2x">
                                <h3>Paragraph</h3>
                                <?php CssCustomizerV1::getTyporaphyCss(
                                        'p', 
                                        'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner]', 
                                        $css_row['inner']['p'], $idx
                                    );
                                    CssCustomizerV1::getContainerCss('p', 
                                        'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner]', 
                                        $css_row['inner']['p'], $idx);
                                ?>
                            </div>
                            <div id="my-wordpress-tabs-3x">
                                <h3>H1</h3>
                                <?php CssCustomizerV1::getTyporaphyCss(
                                        'h1', 
                                        'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner]', 
                                        $css_row['inner']['h1'], $idx
                                    );
                                    CssCustomizerV1::getContainerCss('h1', 
                                        'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner]', 
                                        $css_row['inner']['h1'], $idx);                                
                                ?>
                            </div>                      
                            <div id="my-wordpress-tabs-4x">
                                <h3>H2</h3>
                                <?php CssCustomizerV1::getTyporaphyCss(
                                        'h2', 
                                        'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner]', 
                                        $css_row['inner']['h2'], $idx
                                    );
                                    CssCustomizerV1::getContainerCss('h2', 
                                        'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner]', 
                                        $css_row['inner']['h2'], $idx);                                
                                ?>
                            </div>                            
                            <div id="my-wordpress-tabs-5x">                         
                                <h3>H3</h3>
                                <?php CssCustomizerV1::getTyporaphyCss(
                                        'h3', 
                                        'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner]', 
                                        $css_row['inner']['h3'], $idx
                                    );
                                    CssCustomizerV1::getContainerCss('h3', 
                                        'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner]', 
                                        $css_row['inner']['h3'], $idx);                                
                                ?>
                            </div>                      
                            <div id="my-wordpress-tabs-6x">
                                <h3>H4, H5, H6</h3>
                                <?php CssCustomizerV1::getTyporaphyCss(
                                        'h4', 
                                        'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner]', 
                                        $css_row['inner']['h4'], $idx
                                    );
                                    CssCustomizerV1::getContainerCss('h4', 
                                        'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner]', 
                                        $css_row['inner']['h4'], $idx);                                
                                ?>
                            </div>      
                            <div id="my-wordpress-tabs-7x">
                                <div class="cose-tabs">
                                    <ul>
                                        <li><a href="#link-tab">Link</a></li>
                                        <li><a href="#link-hover-tab">Link Hover</a></li>
                                    </ul>
                                    <div id="link-tab">
                                        <?php //CssCustomizerV1::getLinkCss('a', $field_prefix, $values['a'], $idx) ?>
                                        <?php CssCustomizerV1::getTyporaphyCss(
                                            'a', 
                                            'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner]', 
                                            $css_row['inner']['a'], $idx
                                        );
                                    CssCustomizerV1::getContainerCss('a', 
                                        'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner]', 
                                        $css_row['inner']['a'], $idx);                                        
                                        ?>
                                    </div>
                                    <div id="link-hover-tab">
                                        <?php //CssCustomizerV1::getLinkHoverCss('a-colon-hover', $field_prefix, $values['a-colon-hover'], $idx) ?>
                                        <?php CssCustomizerV1::getTyporaphyCss(
                                                'a-colon-hover', 
                                                'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner]', 
                                                $css_row['inner']['a-colon-hover'], $idx
                                            );
                                    CssCustomizerV1::getContainerCss('a-colon-hover', 
                                        'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner]', 
                                        $css_row['inner']['a-colon-hover'], $idx);                                        
                                        ?>
                                    </div>                            
                                </div>                            
                            </div>                                 
                            <div id="my-wordpress-tabs-8x">
                                <?php CssCustomizerV1::getListCss('ul', 'b[' . $this->_rows_css_option_name . '][' . $idx . '][inner]', $css_row['inner']['ul'], $idx) ?>                                
                            </div>
                        </div>                                                                                   
                        <br />
                        <?php 
                            $save_row_prefix = 'b[' . $this->_rows_css_option_name . '][' . $idx . ']'; 
                            $save_row_id = $idx;
                        ?>
                        <a href="#" class="button-primary save" data-row-id="<?php echo $save_row_id ?>" data-row-prefix="<?php echo $save_row_prefix ?>">Save Styles</a>
                        <a href="#" class="button-primary save-done" data-row-id="<?php echo $save_row_id ?>" data-row-prefix="<?php echo $save_row_prefix ?>">Save Styles and Close</a>
                    </div>
                </div>
            </div>    
<?php
    }    
    
    //>>>>>>>>>>>>>>>>>>>>>>>>    START: STATIC     >>>>>>>>>>>>>>>>>>>>>>>>>     
    
    public static function getLinkHoverCss($tag, $name, $values = array()) 
    {
        $idx = uniqid('id-');
    ?>
        <div>
            <label>Background</label>
            <div class="uploader">
                <input id="slide-image-<?php echo $idx ?>" 
                       name="<?php echo $name ?>[<?php echo $tag ?>][bi]" 
                       type="text"  value="<?php echo $values['bi'] ?>" />
                <input class="button media-uploader" rel="slide-image-<?php echo $idx ?>"type="button" value="Upload" />
            </div>
            <div class="color-picker2">
                <label style="display: none" class="field-label">Color</label>
                <input name="<?php echo $name ?>[<?php echo $tag ?>][bc]"
                       class="wp-color-picker2"  value="<?php echo $values['bc'] ?>" />
            </div>                                          
        </div>
        <div>
            <label><span style="color: #aaa; font-size: 12px;">-</span></label>
            <?php echo CssCustomizerV1::showBgSizeOptions($name . '[' . $tag . '][bs]', $values['bs']) ?>
            <?php echo CssCustomizerV1::showBgRepeatOptions($name . '[' . $tag . '][br]', $values['br']) ?>
        </div>           
        <div>
            <label>Font Weight:</label> 
            <?php echo CssCustomizerV1::showDropDownOptions($name . '[' . $tag . '][fw]', CssCustomizerV1::$font_weight_options, $values['fw']) ?>
        </div>   
        <div>
            <label>Font Style:</label> 
            <?php echo CssCustomizerV1::showDropDownOptions($name . '[' . $tag . '][fs]', CssCustomizerV1::$font_style, $values['fs'])?>
        </div>              
        <div>
            <label>Text Decoration:</label> 
            <?php echo CssCustomizerV1::showDropDownOptions($name . '[' . $tag . '][td]', CssCustomizerV1::$text_decoration, $values['td'])?>
        </div>                                        
        <div>
            <label>Color:</label> 
            <div class="color-picker2">
                <label style="display: none" class="field-label">Color</label>
                <input name="<?php echo $name . '[' . $tag . '][color]' ?>"
                       class="wp-color-picker2"  value="<?php echo $values['color'] ?>" />
            </div>    
        </div>     
    <?php
    }
    
    public static function getContainerCss($tag, $name, $values = array(), $idx = '')
    {
        $idx = $idx . '-' . $tag;
    ?>
        <div class="form-row">
            <label>Width</label> 
            <?php echo CssCustomizerV1::getMeasurementSimple(
                    $name . '[' . $tag . '][w', 
                    array('value' => $values['w'], 'mt' => $values['w-mt']), 
                    'w' . $idx, 0, 100 );
            ?>                                     
        </div>           
        <div class="form-row">
            <label>Height</label> 
            <?php echo CssCustomizerV1::getMeasurementSimple(
                    $name . '[' . $tag . '][h', 
                    array('value' => $values['h'], 'mt' => $values['h-mt']), 
                    'h' . $idx, 0, 100 );
            ?>                                        
        </div> 
         <div class="form-row">
            <label>Alignment</label> 
            <?php echo CssCustomizerV1::showDropDownOptions($name . '[' . $tag . '][a]', 
                    array('l' => 'left', 'r' => 'right', 'c' => 'center'), $values['a']) ?>
        </div>    
        <!--<div class="form-row">
            <label>Position</label> 
            <em>coming soon!</em>
        </div>         
        <div class="form-row">
            <label>Absolute:</label> 
            <em>coming soon!</em>
        </div>-->
        <div class="form-row">
            <label>Background</label>
            <div class="uploader">
                <input id="slide-image-<?php echo $idx ?>" 
                       name="<?php echo $name ?>[<?php echo $tag ?>][bi]" 
                       type="text"  value="<?php echo $values['bi'] ?>" />
                <input class="button media-uploader" rel="slide-image-<?php echo $idx ?>"type="button" value="Upload" />
            </div>
            <div class="color-picker2">
                <label style="display: none" class="field-label">Color</label>
                <input name="<?php echo $name ?>[<?php echo $tag ?>][bc]"
                       class="wp-color-picker2"  value="<?php echo $values['bc'] ?>" />
            </div>             
        </div>
        <div class="form-row background">
            <label><span style="color: #aaa; font-size: 12px;">-</span></label>
            <?php echo CssCustomizerV1::showBgSizeOptions($name . '[' . $tag . '][bs', $values['bs1'], $values['bs2']) ?>
            <?php echo CssCustomizerV1::showBgRepeatOptions($name . '[' . $tag . '][br]', $values['br']) ?>
        </div>
        <div class="form-row background">
            <label><span style="color: #aaa; font-size: 12px;">-</span></label>
            <?php echo CssCustomizerV1::showBgPositionOptions($name . '[' . $tag . '][bp', $values['bp1'], $values['bp2']) ?>
            <?php echo CssCustomizerV1::showDropDownOptions($name . '[' . $tag . '][ba]', CssCustomizerV1::$background_attachment, $values['ba']) ?>
        </div>         
        <!--<div class="form-row">
            <label>Gradient Background</label>
            <em>coming soon!</em>
        </div>-->
        <div class="form-row">
            <label>Border Width</label>  
            <?php echo CssCustomizerV1::getMeasurementBorderRadius(
                    $name . '[' . $tag . '][bow-', 
                    array('top' => $values['bow-pt'],
                          'right' => $values['bow-pr'],
                          'bottom' => $values['bow-pb'],
                          'left' => $values['bow-pl'],
                          'mt' => $values['bow-mt'],
                        ),
                    'bow-' . $idx );
            ?>               
        </div>                
        <div class="form-row">
            <label>Border Style</label> 
            <?php echo CssCustomizerV1::showDropDownOptions($name . '[' . $tag . '][bos]', CssCustomizerV1::$border_style, $values['bos']) ?>
        </div>
        <div class="form-row">
            <label>Border Color</label> 
            <div class="color-picker2">
                <label style="display: none" class="field-label">Color</label>
                <input name="<?php echo $name ?>[<?php echo $tag ?>][boc]"
                       class="wp-color-picker2"  value="<?php echo $values['boc'] ?>" />
            </div>  
        </div>                 
        <div class="form-row">
            <label>Border Radius</label> 
            <?php echo CssCustomizerV1::getMeasurementBorderRadius(
                    $name . '[' . $tag . '][bor-', 
                    array('top' => $values['bor-pt'],
                          'right' => $values['bor-pr'],
                          'bottom' => $values['bor-pb'],
                          'left' => $values['bor-pl'],
                          'mt' => $values['bor-mt'],
                        ),
                    'bor-' . $idx );
            ?>  
        </div>              
        <div class="form-row">
            <label>Margin</label> 
            <?php echo CssCustomizerV1::getMeasurementMarginSliderField(
                    $name . '[' . $tag . '][m-', 
                    array('top' => $values['m-pt'],
                          'right' => $values['m-pr'],
                          'bottom' => $values['m-pb'],
                          'left' => $values['m-pl'],
                          'mt' => $values['m-mt'],
                    ),
                    'm-' . $idx, 0, 100 );
            ?> 
        </div>  
        <div class="form-row">
            <label>Padding</label> 
            <?php echo CssCustomizerV1::getMeasurementPaddingSliderField(
                    $name . '[' . $tag . '][pa-', 
                    array('top' => $values['pa-pt'],
                          'right' => $values['pa-pr'],
                          'bottom' => $values['pa-pb'],
                          'left' => $values['pa-pl'],
                          'mt' => $values['pa-mt'],
                    ), 
                    'pa-' . $idx, 0, 100 );
            ?> 
        </div>          
        <!--<div class="form-row">
            <label>Background Opacity</label> 
            <em>coming soon!</em>
        </div>  
        <div class="form-row">
            <label>Layer</label> 
            <em>coming soon!</em>
        </div>-->
    <?php
    }

    public static function getTyporaphyCss($tag, $name, $values = array(), $idx)
    {
        $idx = $idx . '-' . $tag;
    ?>
        <div>
            <label>Font Size</label> 
            <?php echo CssCustomizerV1::getMeasurementSimple(
                    $name . '[' . $tag . '][fs', 
                    array('value' => $values['fs'], 'mt' => $values['fs-mt']), 
                    'fs' . $idx, 0, 100 );
            ?>
        </div>                              
        <div>
            <label>Font Family</label> 
            <?php
            $font_families = array('', 'arial'=> 'Arial, Helvetica, sans-serif', 'arial-black' => 'Arial Black, Gadget, sans-serif', 
                'comic' => 'Comic Sans MS, cursive, sans-serif', 'impact' => 'Impact, Charcoal, sans-serif', 
                'lucida' => 'Lucida Sans Unicode, Lucida Grande, sans-serif', 'tahoma' => 'Tahoma, Geneva, sans-serif', 
                'trebuchet' => 'Trebuchet MS, Helvetica, sans-serif', 'verdana' => 'Verdana, Geneva, sans-serif', 
                'courier' => 'Courier New, Courier, monospace', 'lucida' => 'Lucida Console, Monaco, monospace',
                'old-standard-tt' => 'Old Standard TT, serif', 'oswald' => 'Oswald, sans-serif', 'open-sans' => 'Open Sans Condensed, sans-serif', 
                'lobster' => 'Lobster, cursive', 'poiret-onece' => 'Poiret One, cursive', 'sigmar-one' => 'Sigmar One, cursive', 
                'sanchez-serif' => 'Sanchez, serif', 'kaushan' => 'Kaushan Script, cursive', 'anton' => 'Anton, sans-serif', 
                'allan' => 'Allan, cursive', 'cinzel' => 'Cinzel, serif', 'righteous' => 'Righteous, cursive', 
                'philosopher' => 'Philosopher, sans-serif', 'tangerine' => 'Tangerine, cursive', 'Bad Script, cursive', 
                'sacramentor' => 'Sacramento, cursive', 'fredericka' => 'Fredericka the Great, cursive', 'raleway' => 'Raleway, sans-serif'
                )
            ?>
            <?php echo CssCustomizerV1::showDropDownOptions($name . '[' . $tag . '][ff]', $font_families, $values['ff'])?>
        </div>
        <div>
            <label>Line Height</label>
            <?php echo CssCustomizerV1::getMeasurementSimple(
                $name . '[' . $tag . '][lh', 
                array('value' => $values['lh'], 'mt' => $values['lh-mt']), 
                'lh' . $idx, 0, 100 );
            ?>  
        </div> 
        <div>
            <label>Text Color</label> 
            <div class="color-picker2">
                <label style="display: none" class="field-label">Color</label>
                <input name="<?php echo $name . '[' . $tag . '][c]' ?>"
                       class="wp-color-picker2"  value="<?php echo $values['c'] ?>" />
            </div>             
        </div>    
        <div class="form-row">
            <label>Text Alignment</label> 
            <?php echo CssCustomizerV1::showDropDownOptions($name . '[' . $tag . '][ta]', 
                    array('c' => 'center', 'l' => 'left', 'r' => 'right'), $values['ta']) ?>
        </div> 
        <div>
            <label>Font Weight</label> 
            <?php echo CssCustomizerV1::showDropDownOptions($name . '[' . $tag . '][fw]', CssCustomizerV1::$font_weight_options, $values['fw']) ?>
        </div>    
        <div>
            <label>Font Style</label> 
            <?php echo CssCustomizerV1::showDropDownOptions($name . '[' . $tag . '][fst]', CssCustomizerV1::$font_style, $values['fst'])?>
        </div> 
        <div>
            <label>Text Decoration</label> 
            <?php echo CssCustomizerV1::showDropDownOptions($name . '[' . $tag . '][td]', CssCustomizerV1::$text_decoration, 
                    $values['td'])?>
        </div>      
          
    <?php
    }
    
    public static function getMeasurementMarginSliderField($field_name, $field_value, $field_id, $min = 0, $max = 100) 
    {
        $type = $field_value['mt'];        
        $top_value = $field_value['top'];
        $right_value = $field_value['right'];
        $bottom_value = $field_value['bottom'];
        $left_value = $field_value['left'];
        
        $html = '<div class="measurement-slider-field">
                 <input type="text" data-value="' . $top_value . '" name="' . $field_name . 'pt]" value="' . $top_value . '" id="' . $field_id . '" class="ui-slider-field" />
                 <input type="text" data-value="' . $top_value . '" name="' . $field_name . 'pr]" value="' . $right_value . '" id="' . $field_id . '" class="ui-slider-field" />
                 <input type="text" data-value="' . $top_value . '" name="' . $field_name . 'pb]" value="' . $bottom_value . '" id="' . $field_id . '" class="ui-slider-field" />
                 <input type="text" data-value="' . $top_value . '" name="' . $field_name . 'pl]" value="' . $left_value . '" id="' . $field_id . '" class="ui-slider-field" />
                 ' . CssCustomizerV1::showDropDownOptions($field_name . 'mt]', array('px' => 'PX', 'em' => 'EM', 'p' => '%'), $type). '
                 </div>';

        return $html;
    }    
    
    public static function getMeasurementSimple($field_name, $field_value = array(), $field_id = '', $min = 0, $max = 100) 
    {
        
        $html = '<div class=" measurement-simple"><input type="text" data-value="' . $field_value['value'] . '" name="' . $field_name . ']" value="' . $field_value['value'] . '" id="' . $field_id . '" class="ui-slider-field" />
                ' . CssCustomizerV1::showDropDownOptions($field_name . '-mt]', array('px' => 'PX', 'em' => 'EM', 'p' => '%'), $field_value['mt']). '</div>';

        return $html;
    }
    
    public static function getMeasurementWithSliderField($field_name, $field_value, $field_id, $min = 0, $max = 100) 
    {
        $mea_field_type = substr($field_value, strlen($field_value) - 2);
        $mea_field_value = ($mea_field_type) ? explode($mea_field_type, $field_value) : $field_value;
        $mea_field_type = ($mea_field_type) ? $mea_field_type : 'px';
        
        $html = '<input type="checkbox" class="enable" data-input-field-id="' . $field_id . '" name="enable" title="If not checked, default values will be used!" ' . 
                (($field_value) ? ' checked="checked"' : '') . ' />
                 <div class="measurement-slider-field ' . (($field_value) ? '' : ' disabled'). '">
                 <input type="hidden" data-value="' . $field_value . '" name="' . $field_name . '" value="' . $field_value . '" id="' . $field_id . '" class="ui-slider-field" />
                 <input type="text" disabled="disabled" name="' . $field_id . '" value="' . $mea_field_value[0] . '" id="' . $field_id . '-temp"  class="ui-slider-field"  />
                ' . CssCustomizerV1::showDropDownOptions($field_id . '-mt', array('px' => 'PX', 'em' => 'EM', 'p' => '%'), $mea_field_type). '
                <div class="otb-ui-slider" id="otb-ui-slider-' . $field_id . '" data-input-field-id="' . $field_id . '" data-min="' . $min . 
                '" data-max="' . $max . '"></div></div>';

        return $html;
    }
    
    public static function getListCss($tag, $name, $values = array())
    {
        $values_ul = $values['ul'];
        $values_li = $values['li'];
    ?>
        <div class="cose-tabs">
            <ul>
                <li><a href="#tab-list">List</a></li>
                <li><a href="#tab-list-item">List Item</a></li>
            </ul>
            <div id="tab-list">
                <div>
                    <label>List Style Position:</label> 
                    <?php echo CssCustomizerV1::showDropDownOptions($name . '[' . $tag . '][lsp]', array('i' => 'inside', 'o' => 'outside'), 
                            $values_ul['lsp']); ?>
                </div>
                <div>
                    <label>List Style Type:</label> 
                    <?php echo CssCustomizerV1::showDropDownOptions($name . '[' . $tag . '][lst]', 
                            array('n' => 'none', 'd' => 'disc', 'c' => 'circle', 'de' => 'decimal', 's' => 'square'),
                            $values_ul['lst']) ?>
                </div>                  
                <?php CssCustomizerV1::getContainerCss($tag, $name, $values_ul, 'list')?>
            </div> 
            <div id="tab-list-item">
                <?php CssCustomizerV1::getTyporaphyCss('li', $name, $values_li, 'list-item')?>  
                <?php CssCustomizerV1::getContainerCss('li', $name, $values_li, 'list-item')?>
            </div>              
        </div>

    <?php
    }

    public static function getMeasurementBorderRadius($field_name, $field_value, $field_id, $min = 0, $max = 100)
    {
        $type = $field_value['mt'];
        
        $top_value = $field_value['top'];
        $right_value = $field_value['right'];
        $bottom_value = $field_value['bottom'];
        $left_value = $field_value['left'];
        
        $html = '<div class="measurement-slider-field">
                 <input type="text" data-value="' . $top_value . '" name="' . $field_name . 'pt]" value="' . $top_value . '" id="' . $field_id . '" class="ui-slider-field" />
                 <input type="text" data-value="' . $top_value . '" name="' . $field_name . 'pr]" value="' . $right_value . '" id="' . $field_id . '" class="ui-slider-field" />
                 <input type="text" data-value="' . $top_value . '" name="' . $field_name . 'pb]" value="' . $bottom_value . '" id="' . $field_id . '" class="ui-slider-field" />
                 <input type="text" data-value="' . $top_value . '" name="' . $field_name . 'pl]" value="' . $left_value . '" id="' . $field_id . '" class="ui-slider-field" />
                 ' . CssCustomizerV1::showDropDownOptions($field_name . 'mt]', array('px' => 'PX', 'em' => 'EM', 'p' => '%'), $type). '
                 </div>';

        return $html;
    }
    
    public static function getMeasurementPaddingSliderField($field_name, $field_value, $field_id, $min = 0, $max = 100)
    {
        $type = $field_value['mt'];
        
        $top_value = $field_value['top'];
        $right_value = $field_value['right'];
        $bottom_value = $field_value['bottom'];
        $left_value = $field_value['left'];
        
        $html = '<div class="measurement-slider-field">
                 <input type="text" data-value="' . $top_value . '" name="' . $field_name . 'pt]" value="' . $top_value . '" id="' . $field_id . '" class="ui-slider-field" />
                 <input type="text" data-value="' . $top_value . '" name="' . $field_name . 'pr]" value="' . $right_value . '" id="' . $field_id . '" class="ui-slider-field" />
                 <input type="text" data-value="' . $top_value . '" name="' . $field_name . 'pb]" value="' . $bottom_value . '" id="' . $field_id . '" class="ui-slider-field" />
                 <input type="text" data-value="' . $top_value . '" name="' . $field_name . 'pl]" value="' . $left_value . '" id="' . $field_id . '" class="ui-slider-field" />
                 ' . CssCustomizerV1::showDropDownOptions($field_name . 'mt]', array('px' => 'PX', 'em' => 'EM', 'p' => '%'), $type). '
                 </div>';

        return $html;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>    END: STATIC     >>>>>>>>>>>>>>>>>>>>>>>>>           

    
    public function getDecimalSliderField($field_name, $field_value, $field_id, $min = 1, $max = 100)
    {
        $html = '<input type="hidden" name="' . $field_name . '" value="' . $field_value . '" id="' . $field_id . '" class="ui-slider-field-decimal" />
                 <input type="text" disabled="disabled" name="' . $field_id . '" value="' . $field_value . '" id="' . $field_id . '-temp"  class="ui-slider-field-decimal"  />
                 <div class="otb-ui-slider-decimal" id="otb-ui-slider-' . $field_id . '" data-input-field-id="' . $field_id . '" data-min="' . $min . 
                '" data-max="' . $max . '"></div>';

        return $html;
    }   
    
    public function getLinkCss($tag, $name, $values = array())
    {
        $idx = uniqid('id-');
    ?>
        <div class="form-row">
            <label>Background</label>
            <div class="uploader">
                <input id="slide-image-<?php echo $idx ?>" 
                       name="<?php echo $name ?>[<?php echo $tag ?>][bi]" 
                       type="text"  value="<?php echo $values['bi'] ?>" />
                <input class="button media-uploader" rel="slide-image-<?php echo $idx ?>"type="button" value="Upload" />
            </div>
            <div class="color-picker2">
                <label style="display: none" class="field-label">Color</label>
                <input name="<?php echo $name ?>[<?php echo $tag ?>][bc]"
                       class="wp-color-picker2"  value="<?php echo $values['bc'] ?>" />
            </div>                                          
        </div>
        <div class="form-row">
            <label><span style="color: #aaa; font-size: 12px;">-</span></label>
            <?php echo CssCustomizerV1::showBgSizeOptions($name . '[' . $tag . '][bs]', $values['bs']) ?>
            <?php echo CssCustomizerV1::showBgRepeatOptions($name . '[' . $tag . '][br]', $values['br']) ?>
        </div>           
        <div>
            <label>Font Weight:</label> 
            <?php echo $this->showDropDownOptions($name . '[' . $tag . '][fw]', CssCustomizerV1::$font_weight_options, $values['fw']) ?>
        </div>   
        <div>
            <label>Font Style:</label> 
            <?php echo $this->showDropDownOptions($name . '[' . $tag . '][fs]', CssCustomizerV1::$font_style, $values['fs'])?>
        </div>              
        <div>
            <label>Text Decoration:</label> 
            <?php echo $this->showDropDownOptions($name . '[' . $tag . '][td]', CssCustomizerV1::$text_decoration, $values['td'])?>
        </div>                                        
        <div>
            <label>Color:</label> 
            <div class="color-picker2">
                <label style="display: none" class="field-label">Color</label>
                <input name="<?php echo $name . '[' . $tag . '][c]' ?>"
                       class="wp-color-picker2"  value="<?php echo $values['c'] ?>" />
            </div>    
        </div>             

    <?php
    }
    
    function showDropDownOptions($name, $options, $selected)
    {
        $select = '<select class="origo-dropdown" name="' . $name . '">';        
        $select .= '<option value="">---</option>';
        foreach ($options as $idx => $val)
        {
            $is_selected = ($idx == $selected) ? ' selected="selected"' : '';
            $select .= '<option value="' . $idx . '" ' . $is_selected . '>' . $val . '</option>';
        }
        $select .= '</select>';
        return $select;     
    }

    function showBgSizeOptions($name, $value1, $value2)
    {
        
        $html = '<input class="ui-slider-field" type="text" name="' . $name . '1]" value="' . $value1 . '" title="Background Image Width: %, px, auto, cover" />';
        $html .= ' <input class="ui-slider-field" type="text" name="' . $name . '2]" value="' . $value2 . '" title="Backgorund Image Height: %, px, auto, cover" />';
        return $html;        
    }

    function showBgRepeatOptions($name, $selected)
    {
        return CssCustomizerV1::showDropDownOptions($name, CssCustomizerV1::$background_repeat, $selected); 
    }    
    
    function showBgPositionOptions($name, $value1, $value2)
    {
        $html = '<input class="ui-slider-field" type="text" name="' . $name . '1]" value="' . $value1 . '" title="Posible Values: top, center, bottom, px, %" />';
        $html .= ' <input class="ui-slider-field" type="text" name="' . $name . '2]" value="' . $value2 . '" title="Posible Values: top, center, bottom, px, %" />';
        return $html;
    }       
}

