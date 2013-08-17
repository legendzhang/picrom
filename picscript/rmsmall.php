<?php
echo "===================================================\n";
if(!is_dir($rootdir)) mkdir($rootdir);
$list = file_list(".",'/\.jpg|\.thm$/i');
deletealltemptyfolder(".");
//var_dump($list);
echo "\n===================================================";

/**
 * Goofy 2011-11-30
 * getDir()去文件夹列表，getFile()去对应文件夹下面的文件列表,二者的区别在于判断有没有“.”后缀的文件，其他都一样
 */

//获取文件目录列表,该方法返回数组
function getDir($dir) {
    $dirArray[]=NULL;
    if (false != ($handle = opendir ( $dir ))) {
        $i=0;
        while ( false !== ($file = readdir ( $handle )) ) {
            //去掉"“.”、“..”以及带“.xxx”后缀的文件
            if ($file != "." && $file != ".."&&!strpos($file,".")) {
                $dirArray[$i]=$file;
                $i++;
            }
        }
        //关闭句柄
        closedir ( $handle );
    }
    return $dirArray;
}

//获取文件列表
function getFile($dir) {
    $fileArray[]=NULL;
    if (false != ($handle = opendir ( $dir ))) {
        while ( false !== ($file = readdir ( $handle )) ) {
            //去掉"“.”、“..”以及带“.xxx”后缀的文件
            if ($file != "." && $file != ".." && strpos($file,".") && strtolower(substr($file,-3,3))=="jpg") {
                $fileArray[]=$dir."/".$file;
            }
        }
        //关闭句柄
        closedir ( $handle );
    }
    return $fileArray;
}

/*
*	递归获取指定路径下的所有文件或匹配指定正则的文件（不包括“.”和“..”），结果以数组形式返回
*	@param	string	$dir
*	@param	string	$pattern
*	@return	array
*/
function deletealltemptyfolder($dir,$pattern="")
{
    global $notFound;
    global $rootdir;
    
    $arr=array();
    $dir_handle=opendir($dir);
    if($dir_handle)
    {
        // 这里必须严格比较，因为返回的文件名可能是“0”
        while(($file=readdir($dir_handle))!==false)
        {
            if($file==='.' || $file==='..')
            {
                continue;
            }
            $tmp=realpath($dir.'/'.$file);
            if(is_dir($tmp))
            {
				unlink($tmp."/Thumbs.db");
				unlink($tmp."/.picasa.ini");
				unlink($tmp."/Picasa.ini");
				unlink($tmp."/NIKON001.DSC");
                $retArr=deletealltemptyfolder($tmp,$pattern);
                if(empty($retArr))
                {
                    rmdir($tmp);
                }
            }
        }
        closedir($dir_handle);
    }
    return $arr;
}

/*
*	递归获取指定路径下的所有文件或匹配指定正则的文件（不包括“.”和“..”），结果以数组形式返回
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
        // 这里必须严格比较，因为返回的文件名可能是“0”
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
					if(filesize($tmp)<300000) {
						if (unlink($tmp)) {
							echo "The file was deleted successfully.", "n";
						} else {
							echo "The specified file could not be deleted. Please try again.", "n";
						}
					}
                }
            }
        }
        closedir($dir_handle);
    }
    return $arr;
}

?> 