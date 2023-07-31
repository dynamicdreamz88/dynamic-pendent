<?php

namespace NAMED_PENDANTS;
class Images {
    private static $instance = null;
    public static function get_instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get_image_directory_params($path) {
        $paths = array();
        if(file_exists($path)) {
            //list each directory
            $fonts_lists = array_diff(scandir($path), array('..', '.'));
            if (!empty($fonts_lists) and is_array($fonts_lists)) {
                foreach ($fonts_lists as $font) {
                    // - possibly fonts
                    $font_path = trailingslashit($path) . $font;

                    if (is_dir($font_path)) {
                        // -- list directories
                        $font_list = array_diff(scandir($font_path), array('..', '.'));
                        if (!empty($font_list) and is_array($font_list)) {
                            foreach ($font_list as $edge) {
                                $paths[] = array(
                                    'font' => $font,
                                    'edge' => $edge
                                );
                            }
                        }
                    }
                }
            }
        }
        return $paths;
    }

    public function create_objects($path) {
        $font = sanitize_title($_POST['font']);
        $edge = sanitize_title($_POST['edge']);
        $edge_path = trailingslashit($path) . trailingslashit($font).trailingslashit($edge);

        echo $edge_path;

        $font_data = array();

        $edges = array_diff(scandir($edge_path),array('..', '.'));
        if(!empty($edges) and is_array($edges)) {
            foreach ($edges as $image) {
                $image_path = trailingslashit($edge_path) . $image;
                if(is_file($image_path)) {
                    // --- scan for files and generate object annotation files
                    // $this->image_object($image_path, $edge_path);
                    //echo $image_path.'<br/>';

                    /*if($image_path==='/opt/lampp/htdocs/wp/wp-content/plugins/pendants/fonts/vegan/beginning/D.png') {*/

                        $_image = imagecreatefrompng($image_path);
                        $font_data[$image] = $this->image_edges( $this->remove_bg( $_image ) , true );

                        /*echo "<pre>";
                        echo ($image_path). '<br/>';
                        print_r($font_data[$image]);
                        die();*/
                    /*}*/
                }
            }
        }

        update_option('pendant_font_'.sanitize_title($font).'_'.sanitize_title($edge) , $font_data );
        return true;
    }

    public function generate_image($name,$font) {
        $base_path = constant('NAMED_PENDANTS_FONTS_DIR');
        $font_path = trailingslashit($base_path) . trailingslashit($font);
        if(!file_exists($font_path)){
            echo "Font Family does not exists!"; die();
        }

        $name_letters = str_split($name);

        $font_data_beginning = get_option('pendant_font_'.sanitize_title( $font).'_beginning');
        $font_data_end = get_option('pendant_font_'.sanitize_title( $font).'_end');

        $font_data_center = get_option('pendant_font_'.sanitize_title( $font).'_center');

        $images = [];
        $positions = array();

        foreach($name_letters as $letter_index=>$latter) {
            if($letter_index===0 and file_exists($font_path.'beginning/'. strtoupper($latter) . '.png')) {
                $images[] = $font_path.'beginning/'. strtoupper($latter) . '.png';
                $positions[] = $font_data_beginning[strtoupper($latter) . '.png'];
            } elseif($letter_index === strlen($name)-1 and file_exists($font_path.'end/'. strtolower($latter) . '.png')) {
                $images[] = $font_path.'end/'. strtolower($latter) . '.png';
                $positions[] = $font_data_end[strtolower($latter) . '.png'];
            } elseif(file_exists($font_path.'center/'. strtolower($latter) . '.png')) {
                $images[] = $font_path.'center/'. strtolower($latter) . '.png';
                $positions[] = $font_data_center[strtolower($latter) . '.png'];
            }
        }

        //$positions = array();
        $width = 0;
        $height = 0;
        $offset_width =0;

        foreach($images as $image_index => $image) {
            if($positions[$image_index]['height'] > $height) {
                $height = $positions[$image_index]['height'];
            }

            $width+=(( $positions[$image_index]['right'] - $positions[$image_index]['left'] )-$offset_width);
        }

        $final_image = imagecreatetruecolor($width+$offset_width, $height);
        $whitebg = imagecolorallocate($final_image, 255, 255, 255);
        //$blue = imagecolorallocate($final_image, 0, 0, 255);

        imagefill($final_image, 0, 0, $whitebg);
        $dest_y = 0;
        $dest_x = 0;

        $previous_positions = false;
        foreach($images as $image_index => $image) {
            $_image = imagecreatefrompng($image);
            //$_whitebg = imagecolorallocatealpha($_image, 255, 255, 255,127);
            //imagefill($_image, 5, 5, $_whitebg);


            /*while (($index = imagecolorclosest( $_image,  0,0,0 ))!==false){

                die();
                imagecolorset($_image,$index,255,255,255); // SET NEW COLOR
            }*/

            $offset_width = 0;

            if(!empty($previous_positions) and !empty($previous_positions['edges'])) {

                $current_positions = $positions[$image_index];
                $offset_width = min( array_map( function ($a,$b) use($previous_positions,$current_positions) {

                    if( $b===0 ) {
                        return $previous_positions['left'] + $current_positions['right'];
                    } else {
                        $previous_right_edge = $previous_positions['right']-$a;
                        if($previous_right_edge <0 ){
                            $previous_right_edge = 0;
                        }
                        return intval(( ($previous_right_edge) + $b));
                    }

                }, array_column( $previous_positions['edges'] ,'r') , array_column( $positions[$image_index]['edges'] ,'l') ) );

                $dest_x+=$previous_positions['right'] - ($offset_width+20);

            } else {
                //$dest_x+=(($positions[$image_index]['right']-$positions[$image_index]['left']) - ($offset_width));
            }

            imagecopy( $final_image, $_image, $dest_x, $dest_y, $positions[$image_index]['left'],0/*$positions[$image_index]['top']*/ ,$positions[$image_index]['right'],$positions[$image_index]['bottom']);
            $previous_positions = $positions[$image_index];

        }
        $alfa_image = imagecreatetruecolor($dest_x+($previous_positions['right']-$previous_positions['left']), $height);
        $whitebg = imagecolorallocate($alfa_image, 255, 255, 255);
        imagefill($alfa_image, 0, 0, $whitebg);
        imagecopy($alfa_image,$final_image,0,0,0,0,$dest_x+($previous_positions['right']-$previous_positions['left']),$height);

        for ($x=0; $x<($dest_x+($previous_positions['right']-$previous_positions['left']));$x++) {
            for($y=0;$y<$height;$y++) {
                $point_color = $this->rgb_at($alfa_image, $x, $y);
                if($point_color['r']<100 and $point_color['g']<100 and $point_color['b']<100) {
                    imagesetpixel($alfa_image , $x, $y,$whitebg);
                }
            }
        }

        //header('Content-Type: image/png');
        if(wp_doing_ajax()) {
            ob_start();
        }
        imagepng($alfa_image);
        if(wp_doing_ajax()) {
            echo base64_encode(ob_get_clean());
        }
        imagedestroy($alfa_image);
    }

    protected function remove_bg($image) {
        $width = imagesx($image);
        $height = imagesy($image);
        //$backgroundColor = imagecolorallocatealpha($image, 255, 255, 255, 127);
        $backgroundColor = imagecolorallocate($image, 255, 255, 255);
        imagecolortransparent($image, $backgroundColor);
        imagefill($image, 0, 0, $backgroundColor);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        return $image;
    }

    protected function rgb_at($image,$x,$y) {
        $rgb = imagecolorat($image, $x, $y);
        $rgba = imagecolorsforindex($image, $rgb);
        $r = $rgba['red'] /*($rgb >> 16) & 0xFF*/;
        $g = $rgba['green'] /*($rgb >> 8) & 0xFF*/;
        $b = $rgba['blue'] /*$rgb & 0xFF*/;
        $a = $rgba['alpha'] /*$rgb & 0xFF*/;
        return array('r'=>$r ,'g' => $g, 'b'=> $b , 'a'=>$a);
    }

    protected function image_edges($image , $edge_diff) {

        $width = imagesx($image);
        $height = imagesy($image);

        // Calculate the boundaries of non-transparent pixels
        $top = 0;
        $bottom = $height - 1;
        $left = $width - 1;
        $right = 0;

        // Find the top boundary
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $point_color = $this->rgb_at($image, $x, $y);
                if( ($point_color['r']<250 or $point_color['g']<250 or $point_color['b']<250) /*or (imagecolorat($image, $x, $y) !== 127 << 24)*/ ) {
                /*if (imagecolorat($image, $x, $y) !== 127 << 24) {*/
                    $top = $y;
                    break 2;
                }
            }
        }

        // Find the bottom boundary
        for ($y = $height - 1; $y >= 0; $y--) {
            for ($x = 0; $x < $width; $x++) {
                $point_color = $this->rgb_at($image, $x, $y);
                if( ($point_color['r']<250 or $point_color['g']<250 or $point_color['b']<250)/* or (imagecolorat($image, $x, $y) !== 127 << 24)*/ ) {
                /*if (imagecolorat($image, $x, $y) !== 127 << 24) {*/
                    $bottom = $y;
                    break 2;
                }
            }
        }

        // Find the left boundary
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $point_color = $this->rgb_at($image, $x, $y);
                if( ($point_color['r']<250 or $point_color['g']<250 or $point_color['b']<250)/* or (imagecolorat($image, $x, $y) !== 127 << 24)*/ ) {
                /*if (imagecolorat($image, $x, $y) !== 127 << 24) {*/
                    $left = $x;
                    break 2;
                }
            }
        }

        // Find the right boundary
        for ($x = $width - 1; $x >= 0; $x--) {
            for ($y = 0; $y < $height; $y++) {
                $point_color = $this->rgb_at($image, $x, $y);
                if( ($point_color['r']<250 or $point_color['g']<250 or $point_color['b']<250)/* or (imagecolorat($image, $x, $y) !== 127 << 24)*/ ) {
                /*if (imagecolorat($image, $x, $y) !== 127 << 24) {*/
                    $right = $x;
                    break 2;
                }
            }
        }

        $edges = array(
            'left' => $left,
            'right' => $right,
            'top' => $top,
            'bottom' => $bottom,
            'width' => $width,
            'height' => $height
        );

        if(!empty($edge_diff)) {
            $edge_data = array();
            for ($y = 0; $y < $height; $y++) {

                if($y<$top or $y>$bottom) {

                    $edge_data[] = array(
                        'l' => $width,
                        'r' => 0,
                    );
                } else {

                    $row_data = array();
                    for ($x = $left; $x < $right/*$width*/; $x++) {
                        $point_color = $this->rgb_at($image, $x, $y);

                        //imagefilledellipse($image, $x,$y, 2, 2,$blue);

                        if ((($point_color['r'] < 250 or $point_color['g'] < 250 or $point_color['b'] < 250)) or $point_color['a'] > 0/* or (imagecolorat($image, $x, $y) !== 127 << 24) */) {
                            $row_data[] = 1;
                        } else {
                            $row_data[] = 0;
                        }
                    }
                    $left_edge = strpos(implode('', $row_data), '11111');
                    $right_edge = strrpos(implode('', $row_data), '11111');
                    if ($left_edge === false or $right_edge === false) {
                        //break 1;
                        /*$edge_data[] = array(
                            'l' => 0,
                            'r' => $width,
                        );*/

                        $edge_data[] = array(
                            'l' => $width,
                            'r' => 0,
                        );

                    } else {
                        $edge_data[] = array(
                            'l' => $left_edge,
                            'r' => $right_edge /*array_search(1,array_reverse($row_data))*/,
                        );
                    }
                    /*imagefilledellipse($image,  $left + strpos(implode('',$row_data),'11111') ,$y, 5, 5, $red);
                    imagefilledellipse($image,  ($left+strrpos(implode('',$row_data),'11111'))  ,$y, 5, 5, $red);*/
                }
            }
            $edges['edges'] = $edge_data;
        }
        //die();

        /*header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
        die;*/
        return $edges;
    }
}