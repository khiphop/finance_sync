<?php
class Autoload_Controller {
	
	/**
	 * 类搜索路径
	 */
	private static $M_class_path = array (
			'application/core/',
			'application/libraries/' 
	);
	
	/**
	 * 用于 类自动载入，不需要手动调用
	 *
	 * @param string $class_name        	
	 */
	public static function setAutoload($class_name) {
		$filename = str_replace ( '\\', DIRECTORY_SEPARATOR, $class_name ) . '.php';
		$filename = ltrim ( $filename, '\\' );
		
		foreach ( self::$M_class_path as $dir ) {
			$path = $dir . $filename;
			
			if (is_file ( $path )) {
				require ($path);
				break;
			}
		}
	}
	
	/**
	 * 注册或取消注册一个自动类载入方法
	 *
	 * @param string $class
	 *        	提供自动载入服务的类
	 * @param boolean $enabled
	 *        	启用或禁用该服务
	 */
	public static function registerAutoload($class = "Autoload_Controller", $enabled = true) {
		if (! function_exists ( 'spl_autoload_register' )) {
			throw new QException ( 'spl_autoload does not exist in this PHP installation' );
		}
		
		if ($enabled === true && $class) {
			spl_autoload_register ( array (
					$class,
					'setAutoload' 
			) );
		} else if ($class) {
			spl_autoload_unregister ( array (
					$class,
					'setAutoload' 
			) );
		}
	}
}
