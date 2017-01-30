<script type="text/javascript" src="<?php echo plugins_url();?>/dseller/vendor/tinymce/tinymce.min.js"></script>
<script type="text/javascript">

    if ( $('textarea').is('#dseller-desc') ) {
        tinymce.init({ selector: "#dseller-desc", width: 500, height: 300, language : 'ru',
            plugins: [
                "advlist autolink autosave link image lists charmap print preview hr anchor pagebreak spellchecker",
                "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                "table contextmenu directionality emoticons template textcolor paste textcolor"
            ],
            image_list: <?php echo getImages();?>,
            toolbar1: "newdocument fullpage | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect formatselect fontselect fontsizeselect",
                toolbar2: "cut copy paste | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link unlink anchor image media code | inserttime preview | forecolor backcolor",
                toolbar3: "table | hr removeformat | subscript superscript | charmap emoticons | print fullscreen | ltr rtl | spellchecker | visualchars visualblocks nonbreaking template pagebreak restoredraft",

                menubar: false,
                code_dialog_width: 1024,
                toolbar_items_size: 'small',

                style_formats: [
                {title: 'Bold text', inline: 'b'},
                {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
                {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
                {title: 'Example 1', inline: 'span', classes: 'example1'},
                {title: 'Example 2', inline: 'span', classes: 'example2'},
                {title: 'Table styles'},
                {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
            ],

                templates: [
                {title: 'Test template 1', content: 'Test 1'},
                {title: 'Test template 2', content: 'Test 2'}
            ]
        });
    }
    
</script>

<?php

    /**
     * Возвращает расширение файла
     * @param $filename
     * @return string
     */
    function getExt($filename){
        return substr($filename, strrpos($filename, '.') + 1);
    }

    /**
     * Возвращает JSON строку, содержащую данные по изображениям
     * в каталоге загрузки WordPress
     * @param null $dir начальный каталог поиска изображений
     * @return string
     */
    function getImages($dir = null){
        static $imageList = array();
        static $count = 0;
        $uploads = wp_get_upload_dir();
        $basedir = $uploads['basedir'];
        $baseurl = $uploads['baseurl'];
        if ($dir == null) $dir = $basedir;
        $exts = array('jpg', 'jpeg', 'png', 'gif');
        $fileList = scandir($dir);
        if (is_array($fileList)){
            foreach($fileList as $filename){
                if ($filename == '.' || $filename == '..') continue;
                if (is_dir($dir . '/' . $filename)){
                    getImages($dir . '/' . $filename);
                }else{
                    if (in_array(getExt($filename), $exts)){
                        $prefix = '';
                        while (isset($imageList[$prefix . $filename])) $prefix .= '_';
                        $imageList[$count]['title'] = $filename;
                        $imageList[$count]['value'] = $baseurl . '/' . substr($dir, strlen($basedir)) . '/' . $filename;
                        $count++;
                    }
                }
            }
        }
        return json_encode($imageList, JSON_UNESCAPED_SLASHES);
    }

?>