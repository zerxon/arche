<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-10-31
 */

class ImageUpload {

    public static function receive($file,$path, $root){
        $savepath = $root.$path;

        //url
        //$rootUrl = 'http://'.$_SERVER['SERVER_NAME'].'/';

        //初始检测
        if($file['error'] > 0){
            $data['status'] = 0;
            switch($file['error']){
                case 1: $data['info'] = '文件大小超过服务器限制';
                    break;
                case 2: $data['info'] = '文件太大！';
                    break;
                case 3: $data['info'] = '文件只加载了一部分！';
                    break;
                case 4: $data['info'] = '文件加载失败！';
                    break;
            }
            return $data;
        }

        //大小检测
        if($file['size'] > 1024*1024){
            $data['status'] = 0;
            $data['info'] = '文件过大！';
            return $data;
        }

        //类型检测
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        //$ext = substr(strrchr($file['name'],"."),1);

        $typeAllow = array('jpg', 'jpeg', 'gif', 'png');

        if( in_array($ext, $typeAllow) ){
            //严格检测
            $imginfo = getimagesize($file['tmp_name']);
            if(empty($imginfo) || ($ext == 'gif' && empty($imginfo['bits']))){
                $data['status'] = 0;
                $data['info'] = '非法图像文件！';
                return $data;
            }
        }else{
            $data['status'] = 0;
            $data['info'] = '文件类型不符！只接受'.implode(',',$typeAllow).'类型图片！';
            return $data;
        }

        //存储
        $time = uniqid();

        if( !is_dir($savepath) ){
            if( !mkdir($savepath, 0777, true) ){
                $data['status'] = 0;
                $data['info'] = '上传目录不存在或不可写！请尝试手动创建:'.$savepath;
                return $data;
            }
        }else{
            if( !is_writable($savepath) ){
                $data['status'] = 0;
                $data['info'] = '上传目录不可写！:'.$savepath;
                return $data;
            }
        }

        $filename = $time .'.'. $ext;
        $upfile = $savepath . $filename;

        if(is_uploaded_file($file['tmp_name'])){
            if(!move_uploaded_file($file['tmp_name'], $upfile)){
                $data['status'] = 0;
                $data['info'] = '移动文件失败！';
                return $data;
            }else{
                $data['status'] = 1;
                $data['info'] = '成功';

                $arr = getimagesize( $upfile );
                $strarr = explode("\"",$arr[3]);//分析图片宽高

                $data['data'] = array(
                    'path'=>$path.$filename,
                    'name'=>$filename,
                    'width'=>$strarr[1],
                    'height'=>$strarr[3]
                );

                return $data;
            }
        }else{
            $data['status'] = 0;
            $data['info'] = '文件丢失或不存在';
            return $data;
        }
    }

    public static function crop($image, $x, $y, $w, $h) {
        // Plug-in 15: Image Crop
        //
        // This plug-in takes a GD image and returns a cropped
        // version of it. If any arguments are out of the
        // image bounds then FALSE is returned. The arguments
        // required are:
        //
        //    $image:   The image source
        //    $x & $y:  The top-left corner
        //    $w & $h : The width and height

        $tw = imagesx($image);
        $th = imagesy($image);

        if ($x > $tw || $y > $th || $w > $tw || $h > $th)
            return FALSE;

        $temp = imagecreatetruecolor($w, $h);
        imagecopyresampled($temp, $image, 0, 0, $x, $y,
            $w, $h, $w, $h);
        return $temp;
    }

    public static function cropper($source_path, $target_width, $target_height)
    {
        $source_info   = getimagesize($source_path);
        $source_width  = $source_info[0];
        $source_height = $source_info[1];
        $source_mime   = $source_info['mime'];
        $source_ratio  = $source_height / $source_width;
        $target_ratio  = $target_height / $target_width;

        // 源图过高
        if ($source_ratio > $target_ratio)
        {
            $cropped_width  = $source_width;
            $cropped_height = $source_width * $target_ratio;
            $source_x = 0;
            $source_y = ($source_height - $cropped_height) / 2;
        }
        // 源图过宽
        elseif ($source_ratio < $target_ratio)
        {
            $cropped_width  = $source_height / $target_ratio;
            $cropped_height = $source_height;
            $source_x = ($source_width - $cropped_width) / 2;
            $source_y = 0;
        }
        // 源图适中
        else
        {
            $cropped_width  = $source_width;
            $cropped_height = $source_height;
            $source_x = 0;
            $source_y = 0;
        }

        switch ($source_mime)
        {
            case 'image/gif':
                $source_image = imagecreatefromgif($source_path);
                break;

            case 'image/jpeg':
                $source_image = imagecreatefromjpeg($source_path);
                break;

            case 'image/png':
                $source_image = imagecreatefrompng($source_path);
                break;

            default:
                return false;
                break;
        }

        $target_image  = imagecreatetruecolor($target_width, $target_height);
        $cropped_image = imagecreatetruecolor($cropped_width, $cropped_height);

        // 裁剪
        imagecopy($cropped_image, $source_image, 0, 0, $source_x, $source_y, $cropped_width, $cropped_height);
        // 缩放
        imagecopyresampled($target_image, $cropped_image, 0, 0, 0, 0, $target_width, $target_height, $cropped_width, $cropped_height);

//        header('Content-Type: image/jpeg');
//        imagejpeg($target_image);
//        imagedestroy($source_image);
//        imagedestroy($target_image);
//        imagedestroy($cropped_image);

        return $target_image;
    }

}