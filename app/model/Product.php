<?php

namespace app\model;

use app\common\helper\Toolkit;
use think\facade\Db;
use think\model\concern\SoftDelete;

class Product extends BaseModel
{

    protected $name = "product";

    protected $deleteTime = 'delete_time';


    const STATUS_FORBIDDEN = 0;
    const STATUS_NORMAL = 1;

    const UNIFIED_SPECIFICATION = 0;
    const MULTIPLE_SPECIFICATIONS = 1;

    public static function statusOptions()
    {
        return [
            self::STATUS_FORBIDDEN => '下架',
            self::STATUS_NORMAL => '上架',
        ];
    }

    public static function isAttributeOptions()
    {
        return [
            self::UNIFIED_SPECIFICATION => '统一规格',
            self::MULTIPLE_SPECIFICATIONS => '多规格',
        ];
    }

    /**
     * 增加/更新商品
     * @param $post 表单提交的数据
     * @param null|Product $productModel 商品模型 有值表示更新
     * @return string|null
     */
    public static function addOrEdit($post, $productModel = null)
    {
        $errMsg = null;
        Db::startTrans();
        try {
            if ($productModel) {
                //更新基本信息
                $productModel->save($post);
                //清除原来的规格相关数据
                ProductSpec::where('product_id', $productModel->id)->delete();
                ProductSpecValue::where('product_id', $productModel->id)->delete();
                ProductSku::where('product_id', $productModel->id)->delete();
            } else {
                //创建商品基础信息
                $post['price'] = $post['price'] ?: 999;
                $productModel = Product::create($post);
            }

            //多规格
            if ($post['is_attribute'] == Product::MULTIPLE_SPECIFICATIONS) {
                if (empty($post['skus'])) {
                    throw new \Exception('SKU数据不能为空');
                }
                $specValueGroups = [];
                $specValueIdMap = [];
                foreach ($post['spec'] as $spec) {
                    //商品规格
                    $specModel = ProductSpec::create([
                        'region_id' => $post['region_id'],
                        'product_id' => $productModel->id,
                        'title' => $spec['title']
                    ]);
                    $specValueIdGroup = [];
                    foreach ($spec['child'] as $specValue) {
                        //商品规格值
                        $specValueModel = ProductSpecValue::create([
                            'region_id' => $post['region_id'],
                            'product_id' => $productModel->id,
                            'spec_id' => $specModel->id,
                            'title' => $specValue['title'],
                            'is_checked' => $specValue['checked'] === 'true' ? 1 : 0,
                        ]);

                        if ($specValue['checked'] === 'true') {
                            $specValueIdMap[$specValue['id']] = [
                                'id' => $specValue['id'],
                                'title' => $specValue['title'],
                                'real_id' => $specValueModel->id
                            ];
                            $specValueIdGroup[] = $specValue['id'];
                        }
                    }

                    if ($specValueIdGroup) {
                        $specValueGroups[] = $specValueIdGroup;
                    }
                }

                $totalStock = 0;
                $minPrice = $minMarketPrice = $minCostPrice = 9999999999;
                foreach (Toolkit::diker($specValueGroups) as $diker) {
                    $skuName = [];
                    $skuData = [];
                    $skuIndexData = [];
                    if (is_array($diker)) {
                        //多规格
                        foreach ($diker as $v) {
                            $skuName[] = $specValueIdMap[$v]['title'];
                            $skuData[] = $specValueIdMap[$v]['real_id'];
                            $skuIndexData[] = $specValueIdMap[$v]['id'];
                        }
                    } else {
                        //单规格
                        $skuName[] = $specValueIdMap[$diker]['title'];
                        $skuData[] = $specValueIdMap[$diker]['real_id'];
                        $skuIndexData[] = $specValueIdMap[$diker]['id'];
                    }

                    $skuIndexStr = join('-', $skuIndexData);
                    if ($post['skus'][$skuIndexStr]['price'] < $minPrice) {
                        $minPrice = $post['skus'][$skuIndexStr]['price'];
                    }
                    if ($post['skus'][$skuIndexStr]['market_price'] < $minMarketPrice) {
                        $minMarketPrice = $post['skus'][$skuIndexStr]['market_price'];
                    }
                    if ($post['skus'][$skuIndexStr]['cost_price'] < $minCostPrice) {
                        $minCostPrice = $post['skus'][$skuIndexStr]['cost_price'];
                    }
                    //商品sku
                    ProductSku::create([
                        'region_id' => $post['region_id'],
                        'product_id' => $productModel->id,
                        'name' => join(' ', $skuName),
                        'picture' => $post['skus'][$skuIndexStr]['picture'],
                        'price' => $post['skus'][$skuIndexStr]['price'],
                        'market_price' => $post['skus'][$skuIndexStr]['market_price'],
                        'cost_price' => $post['skus'][$skuIndexStr]['cost_price'],
                        'stock' => $post['skus'][$skuIndexStr]['stock'],
                        'data' => join('-', $skuData),
                        'status' => $post['skus'][$skuIndexStr]['status'],
                    ]);
                    $totalStock += $post['skus'][$skuIndexStr]['stock'];
                }
                // 更新商品基础信息
                $productModel->save([
                    'stock' => $totalStock,
                    'price' => $minPrice,
                    'market_price' => $minMarketPrice,
                    'cost_price' => $minCostPrice,
                ]);
            }

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $errMsg = $e->getMessage();
        }

        return $errMsg;
    }

    /**
     * 获取商品规格相关数据
     * @param Product $productInfo 商品信息
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function makeSkuTableData($productInfo)
    {
        if ($productInfo['is_attribute']) {
            $specArr = ProductSpec::where('product_id', $productInfo['id'])->select()->toArray();
            $specValueMap = Toolkit::setArray2Index(
                ProductSpecValue::whereIn('spec_id', array_column($specArr, 'id'))->select()->toArray(),
                'spec_id'
            );
            $specData = [];
            foreach ($specArr as $item) {
                $specData[] = [
                    'id' => $item['id'],
                    'title' => $item['title'],
                    'child' => array_map(function ($v) {
                        return [
                            'id' => $v['id'],
                            'title' => $v['title'],
                            'checked' => $v['is_checked'] == 1,
                        ];
                    }, $specValueMap[$item['id']]),
                ];
            }

            $skuArr = ProductSku::where('product_id', $productInfo['id'])->select()->toArray();
            $skuData = [];
            foreach ($skuArr as $item) {
                $skuData['skus[' . $item['data'] . '][picture]'] = $item['picture'];
                $skuData['skus[' . $item['data'] . '][price]'] = $item['price'];
                $skuData['skus[' . $item['data'] . '][market_price]'] = $item['market_price'];
                $skuData['skus[' . $item['data'] . '][cost_price]'] = $item['cost_price'];
                $skuData['skus[' . $item['data'] . '][stock]'] = $item['stock'];
                $skuData['skus[' . $item['data'] . '][status]'] = $item['status'];
            }
        } else {
            $specData = [];
            $skuData = [
                'price' => $productInfo['price'],
                'market_price' => $productInfo['market_price'],
                'cost_price' => $productInfo['cost_price'],
                'stock' => $productInfo['stock'],
            ];
        }

        return ['specData' => $specData, 'skuData' => $skuData];
    }
}
