<?php
/**
 * PHP配置文件的新增、编辑、删除
 * 正则字符串内容进行替换
 * 支持三级数组
 * 数组内数值索引元素在前，关联子数组在后
 * @author Taoser changlin_zhao@qq.com
 * 2022
 */
namespace app\common\lib;

class SetArr
{
	/**
	 * 数组字符串
	 *
	 * @var string
	 */
	protected string $str = '';
	
	function __construct(string $configName)
	{
		/*
		// 自动识别是插件配置还是项目配置
		if(stripos()(__DIR__, 'vendor')){
			// confing/plugin插件配置路径
			$namespace = strtolower(__NAMESPACE__);
			$path = config_path() . '/plugin/'.$namespace.'/'.$configName.'.php';
		} else {
			//config/*.php配置路径
			$path= config_path() . '/' . $configName. ".php";
		}
		*/
		
		// 配置文件名称
		$this->configName = $configName;
		// 文件路径
		$this->configFile = str_replace('\\', '/', app()->getConfigPath() . $configName . '.php');
		//加载配置文件
		$this->str = file_get_contents($this->configFile);
		
	}
	
	/**
	 * 新增配置项，支持三级数组配置
	 *
	 * @param array $arr
	 * @return $this
	 */
	public function add(array $arr)
	{
		//dump($this->str);
		//正则];数组结尾
		$end = '/\];/';
		//一级配置，内容追加到return [一级配置,之后
		$start = $this->getArrSonCount(config($this->configName)) ? '/return\s*\[[^\[|\]]*,\r?\n/' : '/return\s*\[\r?\n?/';
		if(is_array($arr)) {
			foreach ($arr as $k => $v)
			{
				if(!is_array($v)) {
					preg_match($start,$this->str,$arrstart);
					if(is_int($k)) {
						// 数值数组
						if(is_string($v)){
							
							// 类、函数、注释，原样写入
							if(stripos($v,'::class') == false && stripos($v,'()') == false && stripos($v,'//') === false) {
								$reps = $arrstart[0]. "\t'". $v ."',\n";	
							} else {
								$reps = $arrstart[0]. "\t". $v .",\n";
							}
						} elseif(is_bool($v)) {
							$reps = ($v == true) ? $arrstart[0]."\ttrue,\n" : $arrstart[0]."\tfalse,\n";
						} else {
							$reps =  $arrstart[0]."\t".$v . ",\n";
						}
					} else {
						// 键值关联数组
						//判断是否存在KEY
						if (array_key_exists($k,config($this->configName))) {
							echo $k.'不能添加已存在的配置项';
							return false;
						}
						if(is_string($v)){
							if(stripos($v,'::class') == false && stripos($v,'()') == false && stripos($v,'//') === false) {
								$reps = $arrstart[0]."\t'" . $k . "'   => '" . $v ."',\n";
							} else {
								$reps = $arrstart[0]."\t'" . $k . "'   => " . $v .",\n";
							}
						} elseif(is_bool($v)) {
							$reps = ($v == true) ? $arrstart[0]."\t'" . $k . "'	=>   true,\n" : $arrstart[0]."\t'" . $k . "'	=>   false,\n";
						} else {
							$reps = $arrstart[0]."\t'" . $k . "'   => " . $v .",\n";
						}
					}
					$this->str = preg_replace($start, $reps, $this->str);		
				} else {
				// $v是数组，存在多级配置

					// $k不存在，新增二级配置数组, 数组插入];之前
					if (!array_key_exists($k,config($this->configName))) {
						// 拼接数组内元素
						$sonArr = '';
						foreach($v as $kk => $vv) {
							// 1.$vv不是数组，新增配置只有二级元素
							if(!is_array($vv)) {
								if(!is_int($kk)) {
								// 键值对数组
									if(is_int($vv)) {
										$sonArr .= "\t\t'". $kk. "'   => " . $vv . ",\n";
									} elseif(is_bool($vv)) {
										//布尔
										$sonArr .= ($vv == true) ? "\t\t'" . $kk . "'	=>   true,\n" : "\t\t'" . $kk . "'	=>   false,\n";
									} else {
										if(stripos($vv,'::class') == false && stripos($vv,'()') == false && stripos($vv,'//') === false) {
											$sonArr .= "\t\t'". $kk. "'   => '" . $vv . "',\n";
										} else {
											$sonArr .= "\t\t'". $kk. "'   => " . $vv . ",\n";
										}
									}
								} else {
								// 数值数组
									if(is_string($vv)) {
										if(stripos($vv,'::class') == false && stripos($vv,'()') == false && stripos($vv,'//') === false) {
											$sonArr .= "\t\t'" . $vv . "',\n";
										} else {
											$sonArr .= "\t\t" . $vv . ",\n";
										}		
									} elseif(is_bool($vv)) {
										//布尔
										$sonArr .= ($vv == true) ? "\t\ttrue,\n" :"\t\tfalse,\n";
									} else {
										$sonArr .= "\t\t" . $vv . ",\n";
									}
								}
								
							} else { 
							// 2.$vv是数组，新增中包含三级、四级子数组
								$sonArrson = '';
								foreach($vv as $kkk => $vvv) {
									// $vvv不是数组，三级非数组
									if(!is_array($vvv)){
										if(!is_int($kkk)) {
											if(is_string($vvv)) {
												if(stripos($vvv,'::class') == false && stripos($vvv,'()') == false && stripos($vvv,'//') === false) {
													$sonArrson .= "\t\t\t'". $kkk. "'   => '" . $vvv . "',\n";
												} else {
													$sonArrson .= "\t\t\t'". $kkk. "'   => " . $vvv . ",\n";
												}
											} elseif(is_bool($vvv)) {
												$sonArrson .= ($vvv == true) ? "\t\t\t'". $kkk. "'   => true,\n" : "\t\t\t'". $kkk. "'   =>false,\n";
											} else {
												$sonArrson .= "\t\t\t'". $kkk. "'   => " . $vvv . ",\n";
											}
										} else {
											if(is_string($vvv)) {
												if(stripos($vvv,'::class') == false && stripos($vvv,'()') == false && stripos($vvv,'//') === false) {
													$sonArrson .= "\t\t\t'" . $vvv . "',\n";
												} else {
													$sonArrson .= "\t\t\t" . $vvv . ",\n";
												}
											} elseif(is_bool($vvv)) {
												$sonArrson .= ($vvv == true) ? "\t\t\ttrue,\n" : "\t\t\tfalse,\n";
											} else {
												$sonArrson .= "\t\t\t" . $vvv . ",\n";
											}
										}
									} else {
										//$vvv是数组，但不支持子数组
										$sonArrsonArr = '';
										foreach($vvv as $kkkk =>$vvvv) {
											if(is_array($vvvv)) {
												echo $kkkk."不支持四级数组\n";
												return false;
											}

											if(!is_int($kkkk)) {
												if(is_string($vvvv)) {
													if(stripos($vvvv,'::class') == false && stripos($vvvv,'()') == false && stripos($vvvv,'//') === false) {
														$sonArrsonArr .= "\t\t\t\t'". $kkkk. "'   => '" . $vvvv . "',\n";
													} else {
														$sonArrsonArr .= "\t\t\t\t'". $kkkk. "'   => " . $vvvv . ",\n";
													}
												} elseif(is_bool($vvvv)) {
													$sonArrsonArr .= ($vvvv == true) ? "\t\t\t\t'". $kkkk . "'   => true,\n" : "\t\t\t\t'". $kkkk. "'   =>false,\n";
												} else {
													$sonArrsonArr .= "\t\t\t\t'". $kkkk . "'   => " . $vvvv . ",\n";
												}
											} else {
												if(is_string($vvvv)) {
													if(stripos($vvvv,'::class') == false && stripos($vvvv,'()') == false && stripos($vvvv,'//') === false) {
														$sonArrsonArr .= "\t\t\t\t'" . $vvvv . "',\n";
													} else {
														$sonArrsonArr .= "\t\t\t\t" . $vvvv . ",\n";
													}
												} elseif(is_bool($vvvv)) {
													$sonArrsonArr .= ($vvvv == true) ? "\t\t\t\ttrue,\n" : "\t\t\t\tfalse,\n";
												} else {
													$sonArrsonArr .= "\t\t\t\t" . $vvvv . ",\n";
												}
											}

										}
										$sonArrson .=  "\t\t\t'". $kkk. "'   => [\n".$sonArrsonArr."\t\t\t],\n";
										//echo "不支持".$kkkk."是数组";
									}
								}
								$sonArr .=  "\t\t'". $kk. "'   => [\n".$sonArrson."\t\t],\n";
							}
						}
						$reps = "\t'". $k. "'   => [\n".$sonArr."\t],\n];";
						$this->str = preg_replace($end, $reps, $this->str);
					} else {
					// $k已存在，二级配置是数组，需要添加二级或三数组元素
					
					// 把$v的子元素追加在匹配$k => [之后
						// //匹配$k => [ 
						// $kpats = '/\''.$k.'\'\s*=>\s*\[\r?\n/';
						// preg_match($kpats,$this->str,$arrk);
						// 新增二级配置

						// 往数组中追加子元素
						foreach ($v as $kk => $vv) {
							// 1. $vv不是数组
							if(!is_array($vv)) {
								// 新建二级配置

								//匹配$k => [ ***,
								//$kpats = '/\''.$k.'\'\s*=>\s*\[[^\[]*,[^\]]/';
								//$kpats = '/\''.$k.'\'\s*=>\s*\[[^\[|\]]*,\r?\n/';
								// 添加一维数组，数组中有元素匹配到最后一个数组，没有数组，匹配到数组开头[
								// 读$k 数组
								$k_arr = '/\''.$k.'\'\s*=>\s*\[[^\[|\]]*\]/';
								preg_match($k_arr,$this->str,$k_arr);
								//$k_arr[0]
								$k_arr = preg_replace('/[\s|\'|>]/', '', $k_arr[0]);
//dump($k_arr);
								$k_ar = eval('$'.$k_arr);
								$n = $this->getArrSonCount($k_ar);
//dump($n);
								$kpats = $this->getArrSonCount(config($this->configName.'.'.$k)) ? '/\''.$k.'\'\s*=>\s*\[[^\[|\]]*,\r?\n/' : '/\''.$k.'\'\s*=>\s*\[\r?\n/';
								preg_match($kpats,$this->str,$arrk);
//dump($k,$kpats,$this->str,$arrk);
								if(!is_int($kk)) {
									if(array_key_exists($kk,config($this->configName.'.'.$k))) {
										echo $kk.'不能添加已存在的配置项kk';
										return false;
									}
									if(is_string($vv)) {
										if(stripos($vv,'::class') == false && stripos($vv,'()') == false && stripos($vv,'//') === false) {
											$reps = $arrk[0]."\t\t'". $kk. "'   => '" . $vv . "',\n";
										} else {
											$reps = $arrk[0]."\t\t'". $kk. "'   => " . $vv . ",\n";
										}
										
									} elseif(is_bool($vv)) {
										//布尔
										$reps = ($vv == true) ? $arrk[0]."\t\t'" . $kk . "'	=>   true,\n" : $arrk[0]."\t\t'" . $kk . "'	=>   false,\n";
									} else {
										$reps = $arrk[0]."\t\t'". $kk. "'   => " . $vv . ",\n";
									}
								} else {
									if(is_string($vv)) {
										if(stripos($vv,'::class') == false && stripos($vv,'()') == false && stripos($vv,'//') === false) {
											$reps = $arrk[0] ."\t\t'" . $vv . "',\n";
										} else {
											$reps = $arrk[0] ."\t\t" . $vv . ",\n";
										}
										
									} elseif(is_bool($vv)) {
										$reps = ($vv == true) ? $arrk[0] . "\t\ttrue,\n" : $arrk[0] . "\t\tfalse,\n";
									} else {
										$reps = $arrk[0]."\t\t". $vv . ",\n";
									}
								}
								$this->str = preg_replace($kpats, $reps, $this->str);
							} else {
								// 2. $vv是数组

								// 匹配到数组内部的后面
								$sonArrnum = $this->getArrSonNum(config($this->configName.'.'.$k));
								if($sonArrnum > 0) {
									// 数组存在子数组，匹配到最后一个数组],\n后面
									$kpats = '/\''.$k.'\'([^\]]*\]){'.$sonArrnum.'},\r?\n/';
								} else {
									// 没有子数组
									//$kpats = '/\''.$k.'\'\s*=>\s*\[[^\[|\]]*,\r?\n/';
									$kpats = $this->getArrSonCount(config($this->configName.'.'.$k)) ? '/\''.$k.'\'\s*=>\s*\[[^\[|\]]*,\r?\n/' : '/\''.$k.'\'\s*=>\s*\[\r?\n/';
								}
								preg_match($kpats,$this->str,$arrk);

								// a.$vv数组不存在，新建数组及它的子数组$kkk
								if(!array_key_exists($kk,config($this->configName . '.' . $k))) {

									$sonArrson = '';
									foreach($vv as $kkk => $vvv) {
										// $vvv不是数组
										if(!is_array($vvv)) {
											if(!is_int($kkk)) {
												if(is_string($vvv)) {
													if(stripos($vvv,'::class') == false && stripos($vvv,'()') == false && stripos($vvv,'//') === false) {
														$sonArrson .= "\t\t\t'". $kkk. "'   => '" . $vvv . "',\n";
													} else {
														$sonArrson .= "\t\t\t'". $kkk. "'   => " . $vvv . ",\n";
													}
													
												} elseif(is_bool($vvv)) {
													//布尔
													$sonArrson .= ($vvv == true) ? "\t\t\t'" . $kkk . "'	=>   true,\n" : "\t\t\t'" . $kkk . "'	=>   false,\n";
												} else {
													$sonArrson .= "\t\t\t'". $kkk. "'   => " . $vvv . ",\n";
												}
											} else {
												if(is_string($vvv)) {
													if(stripos($vvv,'::class') == false && stripos($vvv,'()') == false && stripos($vvv,'//') === false) {
														$sonArrson .= "\t\t\t'" . $vvv . "',\n";
													} else {
														$sonArrson .=  "\t\t\t".$vvv . ",\n";
													}
													
												} elseif(is_bool($vvv)) {
													//布尔
													$sonArrson .= ($vvv == true) ? "\t\t\ttrue,\n" : "\t\t\tfalse,\n";
												} else {
													$sonArrson .= "\t\t\t" . $vvv . ",\n";
												}
											}

										} else {
											// $vvv是数组
											$sonArrsonArr = '';
											foreach($vvv as $kkkk => $vvvv) {
												if(!is_int($kkkk)) {
													if(array_key_exists($kkkk,config($this->configName.'.'.$k.'.'.$kk.'.'.$kkk))) {
														echo $kkkk."已存在?";
														return false;
													}
													if(is_string($vvvv)) {
														if(stripos($vvvv,'::class') == false && stripos($vvvv,'()') == false && stripos($vvvv,'//') === false) {
															$sonArrsonArr .= "\t\t\t\t'". $kkkk. "'   => '" . $vvvv . "',\n";
														} else {
															$sonArrsonArr .= "\t\t\t\t'". $kkkk. "'   => " . $vvvv . ",\n";
														}
														
													} elseif(is_bool($vvvv)) {
														//布尔
														$sonArrsonArr .= ($vvvv == true) ? "\t\t\t\t'" . $kkkk . "'	=>   true,\n" : "\t\t\t\t'" . $kkkk . "'	=>   false,\n";
													} else {
														$sonArrsonArr .= "\t\t\t\t'". $kkkk. "'   => " . $vvvv . ",\n";
													}
												} else {
													if(is_string($vvvv)) {
														if(stripos($vvvv,'::class') == false && stripos($vvvv,'()') == false && stripos($vvvv,'//') === false) {
															$sonArrsonArr .= "\t\t\t\t'" . $vvvv . "',\n";
														} else {
															$sonArrsonArr .= "\t\t\t\t" . $vvvv . ",\n";
														}
														
													} elseif(is_bool($vvvv)) {
														//布尔
														$sonArrsonArr .= ($vvvv == true) ? "\t\t\t\ttrue,\n" : "\t\t\t\tfalse,\n";
													} else {
														$sonArrsonArr .= "\t\t\t\t" . $vvvv . ",\n";
													}
												}
											}
											$sonArrson .= "\t\t\t'".$kkk."'	=> [\n" . $sonArrsonArr . "\t\t\t],\n";
											//echo '不支持4级数组';
										}										
									}
									$reps = $arrk[0]."\t\t'". $kk. "'   => [\n".$sonArrson."\t\t],\n";
									$this->str = preg_replace($kpats, $reps, $this->str);
								} else {	
								// b.$vv数组已存在
									// 匹配包含$k $kk=>[
									// $kkpats = '/\'' . $k . '\'[\s\S]*?(?:'.$kk.')\'[^\[]*\[\r?\n/';
									// preg_match($kkpats,$this->str,$arrkk);
							
									foreach($vv as $kkk => $vvv) {	
										//halt(123,$arrkk[0]);	
										// $vvv不是数组，追加$kkk
										if(!is_array($vvv)) {
											// 匹配包含$k $kk=>[ ***,
											//$kkpats = '/\'' . $k . '\'[\s\S]*?(?:'.$kk.')\'[^\[]*\[\r?\n/';
											//$kkpats = '/\'' . $k . '\'[\s\S]*?(?:'.$kk.')\'\s*=>\s*\[[^\[|\]]*,/';
											$kkpats = $this->getArrSonCount(config($this->configName.'.'.$k.'.'.$kk)) ? '/\''.$kk.'\'\s*=>\s*\[[^\[|\]]*,\r?\n/' : '/\''.$kk.'\'\s*=>\s*\[\r?\n/';
											preg_match($kkpats,$this->str,$arrkk);
										
											if(!is_int($kkk)) {
												//判断第三级key是否存在
												if(array_key_exists($kkk,config($this->configName.'.'.$k.'.'.$kk))) {
													echo $kkk.'不能添加已存在的配置项kkk';
													return false;
												}
												if(is_string($vvv)) {
													if(stripos($vvv,'::class') == false && stripos($vvv,'()') == false && stripos($vvv,'//') === false) {
														$repsk = $arrkk[0]."\t\t\t'". $kkk. "'   => '" . $vvv . "',\n";
													} else {
														$repsk = $arrkk[0]."\t\t\t'". $kkk. "'   => " . $vvv . ",\n";
													}
													
												} elseif(is_bool($vvv)) {
													$repsk = ($vvv == true) ? $arrkk[0]."\t\t\t'" . $kkk . "'	=>   true,\n" : $arrkk[0]."\t\t\t'" . $kkk . "'	=>   false,\n";
												} else {
													$repsk = $arrkk[0]."\t\t\t'". $kkk. "'   => " . $vvv . ",\n";
												}
											} else {
												if(is_string($vvv)) {
													if(stripos($vvv,'::class') == false && stripos($vvv,'()') == false && stripos($vvv,'//') === false) {
														$repsk = $arrkk[0]."\t\t\t'" . $vvv . "',\n";
													} else {
														$repsk = $arrkk[0]."\t\t\t" . $vvv . ",\n";
													}
													
												} elseif(is_bool($vvv)) {
													$repsk = ($vvv == true) ? $arrkk[0]."\t\t\ttrue,\n" : $arrkk[0]."\t\t\tfalse,\n";
												} else {
													$repsk = $arrkk[0]."\t\t\t" . $vvv . ",\n";
												}
											}
											$this->str = preg_replace($kkpats,$repsk, $this->str);
										} else {
											// $vvv是数组,a.数组存在，b.数组不存在

											//a. $kkk数组存在
											if(array_key_exists($kkk,config($this->configName.'.'.$k.'.'.$kk))) {
												//匹配包含$k $kk $kkk 到]的内容
												//$sonPats = '/\'' . $k . '\'[\s\S]*?(?:'.$kk.')\'[\s\S]*?(?:'.$kkk.')\' [^\]]*/';
												//[\s\S]*?(?:'.$kkk.')\'(.*),/
												
												// $k3pats = '/\'' . $kk k. '\'[\s\S]*?(?:'.$kkk.')\'\s*=>\s*\[\r?\n/';
												// 正则到数组内的元素，不包含子数组
												//$k3pats = '/\'' . $kkk . '\'\s*=>\s*\[[^\[|\]]*,\r?\n/';
												$k3pats = $this->getArrSonCount(config($this->configName.'.'.$k.'.'.$kk.'.'.$kkk)) ? '/\''.$kkk.'\'\s*=>\s*\[[^\[|\]]*,\r?\n/' : '/\''.$kkk.'\'\s*=>\s*\[\r?\n/';
												 preg_match($k3pats,$this->str,$arrk3);

												foreach($vvv as $kkkk => $vvvv) {
													preg_match($k3pats,$this->str,$arrk3);
													if(!is_int($kkkk)) {
													// 多维数组
														if(array_key_exists($kkkk,config($this->configName.'.'.$k.'.'.$kk.'.'.$kkk))) {
															echo $kkkk."已存在k4";
															return false;
														}
														if(is_string($vvvv)) {
															if(stripos($vvvv,'::class') == false && stripos($vvvv,'()') == false && stripos($vvvv,'//') === false) {
																$sonReps = $arrk3[0]."\t\t\t\t'". $kkkk . "'   => '" . $vvvv . "',\n";
															} else {
																$sonReps = $arrk3[0]."\t\t\t\t'". $kkkk . "'   => " . $vvvv . ",\n";
															}
															
														} elseif(is_bool($vvvv)) {
															$sonReps = ($vvvv == true) ? $arrk3[0]."\t\t\t\t'" . $kkkk . "'	=>   true,\n" : $arrk3[0]."\t\t\t\t'" . $kkkk . "'	=>   false,\n";
														} else {
															$sonReps = $arrk3[0]."\t\t\t\t'". $kkkk . "'   => " . $vvvv . ",\n";
														}
													} else {
													// 数值数组
														if(is_string($vvvv)) {
															if(stripos($vvvv,'::class') == false && stripos($vvvv,'()') == false && stripos($vvvv,'//') === false) {
																$sonReps = $arrk3[0]."\t\t\t\t'" . $vvvv . "',\n";
															} else {
																$sonReps = $arrk3[0]."\t\t\t\t" . $vvvv . ",\n";
															}
															
														} elseif(is_bool($vvvv)) {
															$sonReps = ($vvvv == true) ? $arrk3[0]."\t\t\t\ttrue,\n" : $arrk3[0]."\t\t\t\tfalse,\n";
														} else {
															$sonReps = $arrk3[0]."\t\t\t\t" . $vvvv . ",\n";
														}
													}
													$this->str = preg_replace($k3pats,$sonReps, $this->str);
												}
											} else {
											// b. $kkk不存在

												// 匹配到数组内部的后面k
												$sonArrnum = $this->getArrSonNum(config($this->configName.'.'.$k.'.'.$kk));
												if($sonArrnum > 0) {
													// 数组存在子数组，匹配到最后一个数组],\n后面
													$kkpats = '/\''.$kk.'\'([^\]]*\]){'.$sonArrnum.'},\r?\n/';
												} else {
													// 没有子数组k
													//$kkpats = '/\''.$kk.'\'\s*=>\s*\[[^\[|\]]*,\r?\n/';
													$kkpats = $this->getArrSonCount(config($this->configName.'.'.$k.'.'.$kk)) ? '/\''.$kk.'\'\s*=>\s*\[[^\[|\]]*,\r?\n/' : '/\''.$kk.'\'\s*=>\s*\[\r?\n/';
												}
												preg_match($kkpats,$this->str,$arrkk);

												$sonArrsonArr = '';
												foreach($vvv as $kkkk => $vvvv) {
													if(is_array($vvvv)) {
														echo '不支持四级数组';
														return false;
													}
													if(!is_int($kkkk)) {
														if(is_string($vvvv)) {
															if(stripos($vvvv,'::class') == false && stripos($vvvv,'()') == false && stripos($vvvv,'//') === false) {
																$sonArrsonArr .= "\t\t\t\t'". $kkkk . "'   => '" . $vvvv . "',\n";
															} else {
																$sonArrsonArr .= "\t\t\t\t'". $kkkk . "'   => " . $vvvv . ",\n";
															}
															
														} elseif(is_bool($vvvv)) {
															$sonArrsonArr .= ($vvvv == true) ? "\t\t\t\t'" . $kkkk . "'	=>   true,\n" : "\t\t\t\t'" . $kkkk . "'	=>   false,\n";
														} else {
															$sonArrsonArr .= "\t\t\t\t'". $kkkk . "'   => " . $vvvv . ",\n";
														}
													} else {
														if(is_string($vvvv)) {
															if(stripos($vvvv,'::class') == false && stripos($vvvv,'()') == false && stripos($vvvv,'//') === false) {
																$sonArrsonArr .= "\t\t\t\t'" . $vvvv . "',\n";
															} else {
																$sonArrsonArr .= "\t\t\t\t" . $vvvv . ",\n";
															}
															
														} elseif(is_bool($vvvv)) {
															$sonArrsonArr .= ($vvvv == true) ? "\t\t\t\ttrue,\n" : "\t\t\t\tfalse,\n";
														} else {
															$sonArrsonArr .= "\t\t\t\t" . $vvvv . ",\n";
														}
													}
												}
												$reps = $arrkk[0]."\t\t\t'".$kkk."'	=> [\n" . $sonArrsonArr . "\t\t\t],\n";
												$this->str = preg_replace($kkpats,$reps, $this->str);
											}
										}										
									}
								}
							}
						}
					}
				}
			}
			//写入配置
			//return file_put_contents($this->configFile, $this->str) ? true : false;

			return $this;
		}
	}

	/**
	 * 编辑配置数组表
	 * 一位数组修改请用delete和add组合
	 * 仅支持键值对的数组修改
	 *
	 * @param array $arr
	 * @return $this
	 */
	public function edit($arr)
	{
		
		// 只能修改有键值对的数组
		if(!is_array($arr)) return false;
			
		foreach($arr as $k => $v) {
			// key不存在
			if(!array_key_exists($k,config($this->configName))) {
				echo $k.'不存在 '; 
				return false;
			}
			// 一级数组修改
			if(!is_array($v)) {
				$pats ='/return\s*\[[^\[|\]]*,\r?\n/';
				preg_match($pats,$this->str,$arr);
				//halt($arr);
				if(is_string($v)){
					// 字符串
					if(stripos($v,'::class') == false && stripos($v,'()') == false && stripos($v,'//') === false) {
						$repk = "'". $k . "'   => '" . $v . "',";
					} else {
						$repk = "'". $k . "'   => " . $v . ",";
					}
				} elseif(is_bool($v)) {
					//布尔
					$repk =  ($v == true) ? "'" . $k . "'	=>   true," : "'" . $k . "'	=>   false,";
				} else {
					// 数字
					$repk = "'". $k . "'   => " . $v .",";
				}
				$patk = '/\'' . $k . '\'(.*),/';
				$reps = preg_replace($patk, $repk, $arr[0]);
				// 正则查找然后替换
				$this->str = preg_replace($pats, $reps, $this->str);
			} else {
				// 正则二级配置
				$pats = '/\'' . $k . '\'\s*=>\s*\[[^\[|\]]*,/';
				// 二级和三级
				foreach($v as $kk => $vv) {
					if(!array_key_exists($kk, config($this->configName.'.'.$k))) {
						echo $kk.'不存在'; 
						return false;
					}
					//二级配置
					if(!is_array($vv)) {
						if(is_string($vv)){
							// 字符串
							if(stripos($vv,'::class') == false && stripos($vv,'()') == false && stripos($vv,'//') === false) {
								$repkk = "'". $kk . "'   => '" . $vv . "',";
							} else {
								$repkk = "'". $kk . "'   => " . $vv . ",";
							}
							
						} elseif(is_bool($vv)) {
							//布尔
							$repkk =  ($vv == true) ? "'" . $kk . "'	=>   true," : "'" . $kk . "'	=>   false,";
						} else {
							// 数字
							$repkk = "'". $kk . "'   => " . $vv .",";
						}
						preg_match($pats,$this->str,$arrk);
						// 正则需要替换的部分
						$patkk = '/\'' . $kk . '\'(.*?),/';
						$reps =  preg_replace($patkk, $repkk, $arrk[0]);
						$this->str = preg_replace($pats, $reps, $this->str);
					} else {
						// 三级配置
						// 正则二级下的三级配置
						//$pats = '/\'' . $kkk . '\'[\s\S]*?(?:'.$kkk.')\'(.*),/';
						$pats = '/\'' . $kk . '\'\s*=>\s*\[[^\[|\]]*,/';
						foreach($vv as $kkk => $vvv) {
							if(!array_key_exists($kkk, config($this->configName.'.'.$k.'.'.$kk))) {
								echo $kkk.'不存在';
								return false;
							}
							if(!is_array($vvv)) {								
								// 类型判断
								if(is_string($vvv)) {
									if(stripos($vvv,'::class') == false && stripos($vvv,'()') == false && stripos($vvv,'//') === false) {
										$arrReps = "'" . $kkk . "'	=> '" . $vvv . "',";
									} else {
										$arrReps = "'" . $kkk . "'	=> " . $vvv . ",";
									}
								} elseif(is_bool($vvv)) {
									//布尔
									$arrReps =  ($vvv == true) ? "'" . $kkk . "'	=>   true," : "'" . $kkk . "'	=>   false,";
								} else {
									$arrReps = "'" . $kkk . "'	=> " . $vvv . ",";
								}
								preg_match($pats,$this->str,$arrkk);
								// 正则需要替换的部分
								$arrPats = '/\'' . $kkk . '\'(.*),/';
								$reps = preg_replace($arrPats, $arrReps, $arrkk[0]);
								$this->str = preg_replace($pats, $reps, $this->str);
							} else {
								// 四级配置
								// 正则$kkk
								//$pats = '/\'' . $kk . '\'[\s\S]*?(?:'.$kkk.')\'[\s\S]*?(?:'.$kkkk.')\'(.*),/';
								$pats = '/\'' . $kkk . '\'\s*=>\s*\[[^\[|\]]*,/';
								foreach($vvv as $kkkk => $vvvv) {
									if(!array_key_exists($kkkk, config($this->configName.'.'.$k.'.'.$kk.'.'.$kkk))){
										echo $kkk.'不存在';
										return false;
									}									
									// 类型判断
									if(is_string($vvvv)) {
										if(stripos($vvvv,'::class') == false && stripos($vvvv,'()') == false) {
											$repskkkk = "'" . $kkkk . "'	=> '" . $vvvv . "',";
										} else {
											$repskkkk = "'" . $kkkk . "'	=> " . $vvvv . ",";
										}
									} elseif(is_bool($vvvv)) {
										//布尔
										$repskkkk =  ($vvvv == true) ? "'" . $kkkk . "'	=>   true," : "'" . $kkkk . "'	=>   false,";
									} else {
										$repskkkk = "'" . $kkkk . "'	=> " . $vvvv . ",";
									}
									preg_match($pats,$this->str,$arrkkk);
									$patskkkk = '/\'' . $kkkk . '\'(.*),/';
									$reps = preg_replace($patskkkk, $repskkkk, $arrkkk[0]);
									$this->str = preg_replace($pats, $reps, $this->str);
								}
							}
							
						}
					}
				}
			}

		}
		//写入配置
		//return  file_put_contents($this->configFile, $this->str) ? true : false;
		return $this;
	}

	/**
	 * 删除配置数组表
	 * 不支持一次性删除整个数组
	 * 子元素被删除会清理空数组
	 *
	 * @param array $arr
	 * @return $this
	 */
	public function delete($arr)
	{
		// 只能修改有键值对的数组
		if(!is_array($arr)) return false;
		
		foreach($arr as $k => $v) {
			// 一级数组修改
			if(!is_array($v)) {
				// 正则开头到,不包含子数组
				$pats ='/return\s*\[[^\[|\]]*,\r?\n/';
				preg_match($pats,$this->str,$arr);
				if(!isset($arr[0])) {
					echo '有误，删除项可能并不存在';
					return false;
				}
				if(is_int($k)) {
					// 一维数组正则
					$patk = $this->getPats($v);
				} else {
					// key不存在
					if(!array_key_exists($k,config($this->configName))) {
						echo $k.'不存在 '; 
						return false;
					}
					$patk = '/[^\n]*\'' . $v . '\'(.*?)\r?\n/';
				}

				// 正则查找然后替换
				$reps =  preg_replace($patk, '', $arr[0]);
				$this->str = preg_replace($pats, $reps, $this->str);
			} else {
				
				// 二级和三级
				foreach($v as $kk => $vv) {
					//二级配置
					if(!is_array($vv)) {
						// 正则二级配置
						$pats = '/\'' . $k . '\'\s*=>\s*\[[^\[|\]]*,\r?\n/';
						preg_match($pats,$this->str,$arrk);
						if(!isset($arrk[0])) {
							echo $k.'有误，可能并不存在';
							return false;
						}
						if(is_int($kk)) {
							$patkk = $this->getPats($vv);
						} else {
							// key不存在
							if(!array_key_exists($kk,config($this->configName.'.'.$k))) {
								echo $kk.'不存在 '; 
								return false;
							}
							// 正则需要替换的部分
							$patkk = '/[^\n]*\'' . $kk . '\'(.*?),\r?\n/';
						}
						$reps =  preg_replace($patkk, '', $arrk[0]);						
						$this->str = preg_replace($pats, $reps, $this->str);
					} else {
						// 三级配置
						
						foreach($vv as $kkk => $vvv) {							
							if(!is_array($vvv)) {
								// 正则二级下的三级配置
								$pats = '/\'' . $kk . '\'\s*=>\s*\[[^\[|\]]*,\r?\n/';
								preg_match($pats,$this->str,$arrkk);
								if(!isset($arrkkk[0])) {
									echo $kk.'有误，可能并不存在';
									return false;
								}
								if(is_int($kkk)) {
									$patkkk = $this->getPats($vvv);
								} else {
									if(!array_key_exists($kkk, config($this->configName.'.'.$k.'.'.$kk))) {
										echo $kkk.'不存在';
										return false;
									}
									$patkkk = '/[^\n]*\'' . $kkk . '\'(.*),\r?\n/';
								}							
								
								// 正则需要替换的部分
								$reps = preg_replace($patkkk, '', $arrkk[0]);
								$this->str = preg_replace($pats, $reps, $this->str);
							} else {
								// 四级配置
																
								foreach($vvv as $kkkk => $vvvv) {
									if(!is_array($vvvv)) {
										// 正则$kkk
										$pats = '/\'' . $kkk . '\'\s*=>\s*\[[^\[|\]]*,\r?\n/';
										preg_match($pats,$this->str,$arrkkk);
										if(!isset($arrkkk[0])) {
											echo $kkk.'有误，可能并不存在';
											return false;
										}
										if(is_int($kkkk)) {
											$patskkkk = $this->getPats($vvvv);
										} else {
											if(!array_key_exists($kkkk, config($this->configName.'.'.$k.'.'.$kk.'.'.$kkk))){
												echo $kkkk.'不存在';
												return false;
											}
											$patskkkk = '/[^\n]*\'' . $kkkk . '\'(.*),\r?\n/';
										}
										
										$reps = preg_replace($patskkkk, '', $arrkkk[0]);
										$this->str = preg_replace($pats, $reps, $this->str);
									} else {
										echo '不支持更多的层级数组';
									}
									
								}
							}
							
						}
					}
				}
			}

			/* 匹配空数组
				*	'key' => [
				*		],
				*/ 
			//$nullArray = '/[^\n]*\'\w+\'\s*=>\s*\[\s*\]{1}\S*\,?\r?\n/m';
			//preg_match($nullArray,$this->str,$arr);
			//$this->str = preg_replace($nullArray, '', $this->str);

		}
		//写入配置
		//return  file_put_contents($this->configFile, $this->str) ? true : false;
		return $this;
	}

	public function put()
	{
		return  file_put_contents($this->configFile, $this->str) ? true : false;
	}

	/**
	 * 获取数组中子元素为数组的个数
	 *
	 * @param [type] $arr
	 * @return integer
	 */
	public function getArrSonNum($arr) :int
	{
		$i = 0;
		foreach ($arr as $val) {
			if(is_array($val)){
				$i++;
				foreach($val as $vv){
					if(is_array($vv)){
						$i++;
						$this->getArrSonNum($vv);
					}
				}
			}
		}
		return $i;
	}

	/**
	 * 获取一维数组在正则公式
	 *
	 * @param [type] $v
	 * @return string
	 */
	public function getPats($v) :string
	{
		if(is_bool($v)){
			//布尔
			$pats = $v ? '/[^\n]*true,\r?\n/' : '/[^\n]*false,\r?\n/';
		} else {
			// 字符串包含\
			if(stripos($v,"\\") !== false) {
				$v = str_replace("\\", "\\\\", $v);
			}
			// 字符串包含/
			if(stripos($v,'/') !== false) {
				$v = str_replace('/', '\/', $v);
			}
			if(stripos($v,'(') !== false) {
				$v = str_replace('(', "\(", $v);
			}
			if(stripos($v,')') !== false) {
				$v = str_replace(')', "\)", $v);
			}
			
			// if(stripos($v,":") !== false) {
			// 	$v = str_replace(":", "\:", $v);
			// }
			// dump($v);
			// if(stripos($v,'.') !== false) {
			// 	$v = str_replace('.', '\.', $v);
			// }
			// dump($v);
			// if(stripos($v,"'") !== false) {
			// 	$v = str_replace('\'', "\'", $v);
			// }
			// dump($v);
			
			// if(stripos($v,'-') !== false) {
			// 	$v = str_replace('-', "\-", $v);
			// }
			// dump($v);
			// if(stripos($v,'>') !== false) {
			// 	$v = str_replace('>', "\>", $v);
			// }
			// dump($v);
			$pats = '/[^\n]*' . $v . '(.*?)\r?\n/';
		}
		return $pats;
	}

	/**
	 * 获取数组内数值数组的个数
	 * 排除数组中元素为数组的元素
	 *
	 * @param array $array
	 * @return boolean
	 */
	public function getArrSonCount(array $array) :bool
	{
		// 数组元素数量
		$count = count($array);
		// 子数组个数
		$i = 0;
		foreach ($array as $val) {
			if(is_array($val)) {
				$i++;
			}
		}
		// 数值数组的数量
		$count = $count - $i;
		if($count > 0) {
			return true;
		} else {
			return false;
		}
	}

	
}
