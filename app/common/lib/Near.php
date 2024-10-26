<?php
namespace app\common\lib;

/**
 * php 通过经纬度选择最近的车位
 * 要通过PHP找到离给定经纬度最近的车位，你可以使用Haversine公式来计算两点之间的大圆距离。
 * 在这个例子中，getNearestParkingSpot 函数接受你的经纬度和一个包含车位信息的数组。每个车位信息是一个包含latitude和longitude键的关联数组。函数使用haversineGreatCircleDistance计算每个车位到指定经纬度的距离，并返回最近的车位。
 * 请确保$parkingSpots数组包含正确的车位经纬度信息，并且在使用前将$myLatitude和$myLongitude设置为你要查找最近车位的位置。
 * 以下是一个PHP函数示例，它计算一个车位数组中距离给定经纬度最近的车位：
 */
class Near
{
    function getNearestParkingSpot($latitude, $longitude, $parkingSpots) {
        $nearestSpot = null;
        $nearestDistance = 0;
     
        foreach ($parkingSpots as $spot) {
            $distance = $this->haversineGreatCircleDistance(
                $latitude,
                $longitude,
                $spot['latitude'],
                $spot['longitude']
            );
     
            if ($nearestSpot === null || $distance < $nearestDistance) {
                $nearestSpot = $spot;
                $nearestDistance = $distance;
            }
        }
     
        return $nearestSpot;
    }
     
    function haversineGreatCircleDistance($latitude1, $longitude1, $latitude2, $longitude2) {
        $earthRadius = 6371; // 单位为公里
     
        $latitude1 = deg2rad($latitude1);
        $latitude2 = deg2rad($latitude2);
        $longitude1 = deg2rad($longitude1);
        $longitude2 = deg2rad($longitude2);
     
        $deltaLatitude = $latitude2 - $latitude1;
        $deltaLongitude = $longitude2 - $longitude1;
     
        $angle = 2 * asin(sqrt(pow(sin($deltaLatitude / 2), 2) + cos($latitude1) * cos($latitude2) * pow(sin($deltaLongitude / 2), 2)));
        return $earthRadius * $angle;
    }
    
}


// // 假设有一个车位数组
// $parkingSpots = [
//     ['latitude' => 37.7749, 'longitude' => -122.4194], // 例如，SF的一个车位
//     ['latitude' => 40.7128, 'longitude' => -74.0060], // 例如，NY的一个车位
//     // ... 更多车位
// ];
 
// // 设置你要比较的经纬度
// $myLatitude = 37.7749;
// $myLongitude = -122.4194;
 
// // 找到最近的车位
// $nearestSpot = getNearestParkingSpot($myLatitude, $myLongitude, $parkingSpots);
 
// // 输出结果
// print_r($nearestSpot);

// 在这个例子中，getNearestParkingSpot 函数接受你的经纬度和一个包含车位信息的数组。每个车位信息是一个包含latitude和longitude键的关联数组。函数使用haversineGreatCircleDistance计算每个车位到指定经纬度的距离，并返回最近的车位。

// 请确保$parkingSpots数组包含正确的车位经纬度信息，并且在使用前将$myLatitude和$myLongitude设置为你要查找最近车位的位置。