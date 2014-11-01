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
        $time = uniqid('upload_');

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

}