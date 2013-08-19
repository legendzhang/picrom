<?php
echo "<hr/>";
$notFound = "Unavailable";
$rootdir="C:\picrom";
if(!is_dir($rootdir)) mkdir($rootdir);
$list = file_list("pics",'/\.jpg$/i');
//var_dump($list);
echo "<hr/>";

/**
 * Goofy 2011-11-30
 * getDir()ȥ�ļ����б�getFile()ȥ��Ӧ�ļ���������ļ��б�,���ߵ����������ж���û�С�.����׺���ļ���������һ��
 */

//��ȡ�ļ�Ŀ¼�б�,�÷�����������
function getDir($dir) {
    $dirArray[]=NULL;
    if (false != ($handle = opendir ( $dir ))) {
        $i=0;
        while ( false !== ($file = readdir ( $handle )) ) {
            //ȥ��"��.������..���Լ�����.xxx����׺���ļ�
            if ($file != "." && $file != ".."&&!strpos($file,".")) {
                $dirArray[$i]=$file;
                $i++;
            }
        }
        //�رվ��
        closedir ( $handle );
    }
    return $dirArray;
}

//��ȡ�ļ��б�
function getFile($dir) {
    $fileArray[]=NULL;
    if (false != ($handle = opendir ( $dir ))) {
        while ( false !== ($file = readdir ( $handle )) ) {
            //ȥ��"��.������..���Լ�����.xxx����׺���ļ�
            if ($file != "." && $file != ".." && strpos($file,".") && strtolower(substr($file,-3,3))=="jpg") {
                $fileArray[]=$dir."/".$file;
            }
        }
        //�رվ��
        closedir ( $handle );
    }
    return $fileArray;
}

/*
*	�ݹ��ȡָ��·���µ������ļ���ƥ��ָ��������ļ�����������.���͡�..�����������������ʽ����
*	@param	string	$dir
*	@param	string	$pattern
*	@return	array
*/
function file_list($dir,$pattern="")
{
    global $notFound;
    global $rootdir;
    
    $arr=array();
    $dir_handle=opendir($dir);
    if($dir_handle)
    {
        // ��������ϸ�Ƚϣ���Ϊ���ص��ļ��������ǡ�0��
        while(($file=readdir($dir_handle))!==false)
        {
            if($file==='.' || $file==='..')
            {
                continue;
            }
            $tmp=realpath($dir.'/'.$file);
            if(is_dir($tmp))
            {
                $retArr=file_list($tmp,$pattern);
                if(!empty($retArr))
                {
                    $arr[]=$retArr;
                }
            }
            else
            {
                if($pattern==="" || preg_match($pattern,$tmp))
                {
                    $arr[]=$tmp;
                    $camera = cameraUsed($tmp);
                    if($camera) {
                        echo $tmp."<p/>";
                        echo $file."<p/>";
                        echo $camera['datetime']."<p/>";
                        //echo $camera['make']."<p/>";
                        //echo $camera['model']."<p/>";
                        $phototime="";
                        if($camera['datetime'] != "Unavailable") {
                          $phototime=$camera['datetime'];
                        } else if ($camera['date'] != "Unavailable") {
                          $phototime=$camera['date'];
                        }
                        if($phototime!="") {
                            $year=substr($phototime,0,4);
                            $month=substr($phototime,5,2);
                            $day=substr($phototime,8,2);
                            $hour=substr($phototime,11,2);
                            $minute=substr($phototime,14,2);
                            $second=substr($phototime,17,2);
                            if($year != "0000"
                                && $month != "00"
                                && $day != "00"
                            ) {
                                $day_dir=$rootdir."/".$year.$month.$day;
                                if(!is_dir($day_dir)) mkdir($day_dir);
                                $dest_filename = $day_dir."/".$year.$month.$day."_".$hour.$minute.$second;
                                $filename=$dest_filename;
                                $i=0;
                                $filesizearray=array();
                                while(1) {
                                    if(is_file($filename.".jpg")) {
                                        $filesizearray[]=filesize($filename.".jpg");
                                        $i++;
                                        $filename = $dest_filename."_".$i;
                                    } else {
                                        if($i>0) {
                                            $found=false;
                                            foreach($filesizearray as $filesize) {
                                                if($filesize==filesize($tmp)) {
                                                    $found=true;
                                                    break;
                                                }
                                            }
                                            if(!$found) {
                                                copy($tmp,$filename.".jpg");
                                                echo "copy to: ".$filename.".jpg"."<p/><p/><p/>";
                                            } else {
                                                echo "duplicate.......... <p/><p/><p/>";
                                            }
                                        } else {
                                            copy($tmp,$filename.".jpg");
                                            echo "copy to: ".$filename.".jpg"."<p/><p/><p/>";
                                        }
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        closedir($dir_handle);
    }
    return $arr;
}


// This function is used to determine the camera details for a specific image. It returns an array with the parameters.
function cameraUsed($imagePath) {

    // Check if the variable is set and if the file itself exists before continuing
    if ((isset($imagePath)) and (file_exists($imagePath))) {
    
      // There are 2 arrays which contains the information we are after, so it's easier to state them both
      $exif_ifd0 = read_exif_data($imagePath ,'IFD0' ,0);       
      $exif_exif = read_exif_data($imagePath ,'EXIF' ,0);
      
      //error control
      $notFound = "Unavailable";
      
      // Make 
      if (@array_key_exists('Make', $exif_ifd0)) {
        $camMake = $exif_ifd0['Make'];
      } else { $camMake = $notFound; }
      
      // Model
      if (@array_key_exists('Model', $exif_ifd0)) {
        $camModel = $exif_ifd0['Model'];
      } else { $camModel = $notFound; }
      
      // Exposure
      if (@array_key_exists('ExposureTime', $exif_ifd0)) {
        $camExposure = $exif_ifd0['ExposureTime'];
      } else { $camExposure = $notFound; }

      // Aperture
      if (@array_key_exists('ApertureFNumber', $exif_ifd0['COMPUTED'])) {
        $camAperture = $exif_ifd0['COMPUTED']['ApertureFNumber'];
      } else { $camAperture = $notFound; }
      
      // Date
      if (@array_key_exists('DateTime', $exif_ifd0)) {
        $camDate = $exif_ifd0['DateTime'];
      } else { $camDate = $notFound; }
      
      // ISO
      if (@array_key_exists('ISOSpeedRatings',$exif_exif)) {
        $camIso = $exif_exif['ISOSpeedRatings'];
      } else { $camIso = $notFound; }
      
      // ISO
      if (@array_key_exists('DateTimeOriginal',$exif_exif)) {
        $camDateTime = $exif_exif['DateTimeOriginal'];
      } else { $camDateTime = $notFound; }

      $return = array();
      $return['make'] = $camMake;
      $return['model'] = $camModel;
      $return['exposure'] = $camExposure;
      $return['aperture'] = $camAperture;
      $return['date'] = $camDate;
      $return['iso'] = $camIso;
      $return['datetime'] = $camDateTime;
      return $return;
    
    } else {
      return false; 
    } 
}

?> 