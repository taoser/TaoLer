/**
  images压缩扩展模块
  changlin_zhao@qq.com
  2021.5.25
**/      
 
layui.define(['upload','layer'],function(exports){ 
var upload = layui.upload;
var layer = layui.layer;
var compressImage = {
	uploads:  function(obj){
	//obj.preview(function(index, file, result){
				 
		//执行实例
					
		var files = obj.pushFile();
		var filesArry = [];
		for (var key in files) { //将上传的文件转为数组形式
		  filesArry.push(files[key])
		}
		var index = filesArry.length - 1;
		var file = filesArry[index]; //获取最后选择的图片,即处理多选情况
 
		if (navigator.appName == "Microsoft Internet Explorer" && parseInt(navigator.appVersion.split(";")[1]
			.replace(/[ ]/g, "").replace("MSIE", "")) < 9) {
		  return obj.upload(index, file)
		}
		canvasDataURL(file, function (blob) {
		  var aafile = new File([blob], file.name, {
			type: file.type
		  })
		  var isLt1M;
		  if (file.size < aafile.size) {
			isLt1M = file.size
		  } else {
			isLt1M = aafile.size
		  }
 
		  if (isLt1M / 1024 / 1024 > 2) {
			return layer.alert('上传图片过大！')
		  } else {
			if (file.size < aafile.size) {
			  return obj.upload(index, file)
			}
			obj.upload(index, aafile)
		  }
		})
					  
					
				 
				 
	  function canvasDataURL(file, callback) { //压缩转化为base64
		var reader = new FileReader()
		reader.readAsDataURL(file)
		reader.onload = function (e) {
		  const img = new Image()
		  const quality = 0.8 // 图像质量
		  const canvas = document.createElement('canvas')
		  const drawer = canvas.getContext('2d')
		  img.src = this.result
		  img.onload = function () {
		  
		  var originWidth = img.width,/* 图片的宽度 */
		  originHeight = img.height; /* 图片的高度 */
		  
		  // 设置最大尺寸限制，将所有图片都压缩到小于1m
			const maxWidth = 2560, maxHeight = 1600;
			// 需要压缩的目标尺寸
			let targetWidth = originWidth, targetHeight = originHeight;
			// 等比例计算超过最大限制时缩放后的图片尺寸
			if (originWidth > maxWidth || originHeight > maxHeight) {
				if (originWidth / originHeight > 1) {
					// 宽图片
					targetWidth = maxWidth;
					targetHeight = Math.round(maxWidth * (originHeight / originWidth));
				} else {
					// 高图片
					targetHeight = maxHeight;
					targetWidth = Math.round(maxHeight * (originWidth / originHeight));
				}
			}
				
			canvas.width = targetWidth;
			canvas.height = targetHeight;
			drawer.drawImage(img, 0, 0, canvas.width, canvas.height)
			convertBase64UrlToBlob(canvas.toDataURL(file.type, quality), callback);
		  }
		}
	  }
	 
	  function convertBase64UrlToBlob(urlData, callback) { //将base64转化为文件格式
		const arr = urlData.split(',')
		const mime = arr[0].match(/:(.*?);/)[1]
		const bstr = atob(arr[1])
		let n = bstr.length
		const u8arr = new Uint8Array(n)
		while (n--) {
		  u8arr[n] = bstr.charCodeAt(n)
		}
		callback(new Blob([u8arr], {
		  type: mime
		}));
	  }

		
	//})
	}
 }		
  //输出 imgcom 接口
  exports('imgcom', compressImage);
});    
      
