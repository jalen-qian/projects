<?php

namespace MyApp\Library;

use \Phalcon\Mvc\User\Component;
use MyApp\Library\Utilities;
/**
 * MyApp\Library\Xhttp
 * Xhttp 
 */
class XHttp extends Component
{
	/**
	 * Http Get Request
	 * @param string $url_mixed
	 * @param array $data
	 * @param string $debug 
	 * @param string $type
	 * @param string $overTime 
	 * @return array
	 */
	public function get($urlMixed, $data = array(), $debug = false,$header = array(), $overTime = 15) {
		
		$curl = new XCurl;
		if (is_array($header) && $header) {
			foreach ($header as $key => $value) {
				$curl->setHeader($key, $value);
			}
		}
		$curl->setOpt(CURLOPT_TIMEOUT, $overTime);
		$curl->get($urlMixed, $data);
		if ($debug == true) {
			//XUtils::dump($curl->request_headers);
		}

		return $curl->response;
	}
	/**
	 * Http Post Request
	 * @param string $url_mixed
	 * @param array $data
	 * @param string $debug 
	 * @param string $type
	 * @param string $overTime 
	 * @return array
	 */
	public function post($urlMixed, $data = array(), $debug = false, $header = array(), $overTime = 15) {
		
		$curl = new XCurl();
		$curl->setOpt(CURLOPT_TIMEOUT, $overTime);
		if (is_array($header) && $header) {
			foreach ($header as $key => $value) {
				$curl->setHeader($key, $value);
			}
		}
		$curl->post($urlMixed, $data);
		if ($debug == true) {
			//XUtils::dump($curl->request_headers);
		}

		return $curl->response;
	}
	/**
	 * 异步上传文件
	 * @param string $url
	 * @param string $filename
	 * @param string $path
	 * @param string $type
	 * @return array
	 */
	public function upload($filename, $path, $type = '.jpg',$dfsType = 7)
	{
		
		if (function_exists('curl_file_create')) {
			$cFile = curl_file_create($path, $type, $filename);
		} else {
			$cFile = '@' . realpath($path) . ";type=" . $type . ";filename=" . $filename;
		}
		//token，安全验证
		$dfsToken = md5($this->config['params']['apiDesc']['dfsApiDesc']['backenKey']).'####'.date('Y-m-d H:i:s');
		$utilities = new Utilities;
		$dfsToken = $utilities->apiSTD3Encrypt($dfsToken, 'dfsApiDesc');
		
		$data = array('file' => $cFile, 'dfsCdn' => 'cdn6','dfsType' => $dfsType,'dfsToken'=>$dfsToken); //文件路径，前面要加@，表明是文件上传.
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->config['params']['apiUrl']['dfs']);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); //二进制流的方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// curl_getinfo($ch);
		$return_data = curl_exec($ch);
		curl_close($ch);
		return $return_data;
	}
	 
}