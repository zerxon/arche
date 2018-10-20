<?php

class Template {
    var $var_regexp = "\@?\\\$[a-zA-Z_][\\\$\w->\(\)]*(?:\[[\w\-\.\"\'\[\]\$]+\])*";
    var $vtag_regexp = "\<\?php echo (\@?\\\$[a-zA-Z_][\\\$\w->\(\)]*(?:\[[\w\-\.\"\'\[\]\$]+\])*)\;\?\>";
    var $const_regexp = "\{([\w]+)\}";

    public function compile($tplName, $tplPath, $cachePath, $cacheEnable, $cacheExpiry) {
        $cacheFile = $cachePath.$tplName;
        $tplFile = $tplPath.$tplName;

        //如果文件不存在，或关闭了缓存，或现在时间与修改时间之差大于缓存过期时间，则重新渲染模板文件
        if(!file_exists($cacheFile) || !$cacheEnable || (time() - filemtime($cacheFile) > $cacheExpiry)) {
        //if(!file_exists($cacheFile) || filemtime($cacheFile) < filemtime($tplFile)) {
            $template = file_get_contents($tplFile);
            $template = $this->_parse($template);

            makeDirectory(dirname($cacheFile));
            isWriteFile($cacheFile, $template, $mod = 'w', true);
        }

        return $cacheFile;
    }

    private function _importTemplate($tplPath) {
        $viewConfig = C('view');
        $path = VIEW_PATH.$tplPath.$viewConfig['suffix'];

        if (!file_exists($path)) {
        	return "{template file '$path' does not exist.}";
        }
        else {
        	ob_start();
	        include_once $path;
	        $template = ob_get_contents();
	        ob_end_clean();

	        return $template;
        }
        
    }

	private function _parse($template) {

		$template = preg_replace("/\{tsurl(.*?)\}/s", "{php echo tsurl\\1}", $template);
        $template = preg_replace("/\{importTemplate\(\'(.*?)\'\)\}/ies", "\$this->_importTemplate('\\1')", $template);//替换importTemplate标签

        //转换不解析部分
        $matchCount = preg_match_all('/!#([^#][^!]*)#!/',$template, $matchs);
        if($matchCount > 0) {
            $replace = $matchs[1];
            for($index=0; $index<$matchCount; $index++) {
                $template = str_replace($replace[$index], $index, $template);
            }
        }

        $template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);//去除html注释符号<!---->
		$template = preg_replace("/\{($this->var_regexp)\}/", "<?php echo \\1;?>", $template);//替换带{}的变量
		$template = preg_replace("/\{($this->const_regexp)\}/", "<?php echo \\1;?>", $template);//替换带{}的常量
		$template = preg_replace("/(?<!\<\?php echo |\\\\)$this->var_regexp/", "<?php echo \\0;?>", $template);//替换重复的<?php echo
		$template = preg_replace("/\{php (.*?)\}/ies", "\$this->stripvTag('<?php \\1?>')", $template);//替换php标签
		$template = preg_replace("/\{for (.*?)\}/ies", "\$this->stripvTag('<?php for(\\1) {?>')", $template);//替换for标签
		
		$template = preg_replace("/\{elseif\s+(.+?)\}/ies", "\$this->stripvTag('<?php } elseif (\\1) { ?>')", $template);//替换elseif标签
		for($i=0; $i<3; $i++) {
			$template = preg_replace("/\{loop\s+$this->vtag_regexp\s+$this->vtag_regexp\s+$this->vtag_regexp\}(.+?)\{\/loop\}/ies", "\$this->loopSection('\\1', '\\2', '\\3', '\\4')", $template);
			$template = preg_replace("/\{loop\s+$this->vtag_regexp\s+$this->vtag_regexp\}(.+?)\{\/loop\}/ies", "\$this->loopSection('\\1', '', '\\2', '\\3')", $template);
		}
		$template = preg_replace("/\{if\s+(.+?)\}/ies", "\$this->stripvTag('<?php if(\\1) { ?>')", $template);//替换if标签
		$template = preg_replace("/\{block (.*?)\}/ies", "\$this->stripBlock('\\1')", $template);//替换block标签
		$template = preg_replace("/\{else\}/is", "<?php } else { ?>", $template);//替换else标签
		$template = preg_replace("/\{\/if\}/is", "<?php } ?>", $template);//替换/if标签
		$template = preg_replace("/\{\/for\}/is", "<?php } ?>", $template);//替换/for标签
		$template = preg_replace("/$this->const_regexp/", "<?php echo \\1;?>", $template);//note {else} 也符合常量格式，此处要注意先后顺??
		$template = preg_replace("/(\\\$[a-zA-Z_]\w+\[)([a-zA-Z_]\w+)\]/i", "\\1'\\2']", $template);//将二维数组替换成带单引号的标准模式
		$template = "$template";

        //还原不解析部分
        if($matchCount > 0) {
            for($index=0; $index<$matchCount; $index++) {
                $template = str_replace("!#$index#!", $replace[$index], $template);
            }
        }

        return $template;
	}

	/**
	 * 正则表达式匹配替换
	 *
	 * @param string $s ：
	 * @return string
	 */
	private function stripvTag($s) {
		return preg_replace("/$this->vtag_regexp/is", "\\1", str_replace("\\\"", '"', $s));
	}

	function stripTagQuotes($expr) {
		$expr = preg_replace("/\<\?php echo (\\\$.+?);\?\>/s", "{\\1}", $expr);
		$expr = str_replace("\\\"", "\"", preg_replace("/\[\'([a-zA-Z0-9_\-\.\x7f-\xff]+)\'\]/s", "[\\1]", $expr));
		return $expr;
	}

    private function stripv($vv){
		$vv = str_replace('<?php','',$vv);
		$vv = str_replace('echo','',$vv);
		$vv = str_replace(';','',$vv);
		$vv = str_replace('?>','',$vv);
		return $vv;
	}
	
	/**
	 * 将模板中的块替换成BLOCK函数
	 *
	 * @param string $blockname ：
	 * @param string $parameter ：
	 * @return string
	 */
    private function stripBlock($parameter) {
		return $this->stripTagQuotes("<?php Mooblock(\"$parameter\"); ?>");
	}

	/**
	 * 替换模板中的LOOP循环
	 *
	 * @param string $arr ：
	 * @param string $k ：
	 * @param string $v ：
	 * @param string $statement ：
	 * @return string
	 */
    private function loopSection($arr, $k, $v, $statement) {
		$arr = $this->stripvTag($arr);
		$k = $this->stripvTag($k);
		$v = $this->stripvTag($v);
		$statement = str_replace("\\\"", '"', $statement);
		return $k ? "<?php foreach((array)$arr as $k=>$v) {?>$statement<?php }?>" : "<?php foreach((array)$arr as $v) {?>$statement<?php } ?>";
	}
}