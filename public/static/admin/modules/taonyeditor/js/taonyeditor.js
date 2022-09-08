
layui.define(['layer', 'upload'], function(exports){
  
    var $ = layui.jquery
    ,layer = layui.layer
    ,device = layui.device();
  
    //阻止IE7以下访问
    if(device.ie && device.ie < 8){
      layer.alert('如果您非得使用 IE 浏览器访问社区，那么请使用 IE8+');
    }

    const image_upload_handler = (blobInfo, progress) => new Promise((resolve, reject) => {
      const xhr = new XMLHttpRequest();
      xhr.withCredentials = false;
      xhr.open('POST', taonyUploadImgage);
    
      xhr.upload.onprogress = (e) => {
        progress(e.loaded / e.total * 100);
      };
    
      xhr.onload = () => {
        if (xhr.status === 403) {
          reject({ message: 'HTTP Error: ' + xhr.status, remove: true });
          return;
        }
    
        if (xhr.status < 200 || xhr.status >= 300) {
          reject('HTTP Error: ' + xhr.status);
          return;
        }
    
        const json = JSON.parse(xhr.responseText);
    
        if (!json || typeof json.location != 'string') {
          //reject('Invalid JSON: ' + xhr.responseText);
          // layer.alert(json.msg,{shift: 6});
          reject(json.msg);
          return;
        }
    
        resolve(json.location);
      };
    
      xhr.onerror = () => {
        reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
      };
    
      const formData = new FormData();
      formData.append('file', blobInfo.blob(), blobInfo.filename());
    
      xhr.send(formData);
    });

    // 文件上传
    const file_picker_callback = function(callback, value, meta) {
      //文件分类
      var filetype='.pdf, .txt, .zip, .rar, .7z, .doc, .docx, .xls, .xlsx, .ppt, .pptx, .mp3, .mp4';
        //后端接收上传文件的地址
        var upurl=taonyUploadUrl;
        //为不同插件指定文件类型及后端地址
        switch(meta.filetype){
            case 'image':
                filetype='.jpg, .jpeg, .png, .gif';
                upurl=taonyUploadImgage;
                break;
            case 'media':
                filetype='.mp3, .mp4';
                upurl=taonyUploadVideo;
                break;
            case 'audio':
              filetype='.zip, .rar, .7z';
              upurl=taonyUploadZip
            default:
        }
        //模拟出一个input用于添加本地文件
        var input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', filetype);
            input.click();
            input.onchange = function() {
            var file = this.files[0];

            var xhr, formData;
            console.log(file.name);
            xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open('POST', upurl);
            xhr.onload = function() {
                var json;
                if (xhr.status != 200) {
                    //failure('HTTP Error: ' + xhr.status);
                    layer.alert(xhr.status,{shift: 6});
                    return;
                }
                json = JSON.parse(xhr.responseText);
                if (!json || typeof json.location != 'string') {
                    //failure('Invalid JSON: ' + xhr.responseText);
                    layer.alert(json.msg,{shift: 6});
                    return;
                }

                //回调地址
                callback(json.location);
            };
            formData = new FormData();
            formData.append('file', file, file.name );
            xhr.send(formData);
          };
    };
    

    $('textarea#L_content').tinymce({
        //selector: mytextareaid,
        language:'zh-Hans',
        branding: false,
        plugins: "lists advlist autolink anchor autosave visualblocks code charmap codesample directionality emoticons image insertdatetime link table media pagebreak wordcount preview axupimgs nonbreaking searchreplace bdmap dlink", //依赖lists插件
        toolbar: "undo redo bold italic forecolor | alignleft aligncenter alignright alignjustify | underline  link dlink | outdent indent bullist numlist | ormatselect code image media axupimgs | blockquote codesample ltr rtl | styles fontfamily fontsize lineheight searchreplace preview",
        icons_url: '/addons/taonyeditor/js/iconfont.js', // load icon pack
        autosave_prefix: "editor-autosave-{path}{query}-{id}-",
        autosave_restore_when_empty: true,
        //图片上传
        relative_urls: false,
        convert_urls: false,
        image_dimensions: false,
        image_prepend_url: imagePrependUrl,
        images_upload_url: taonyUploadImgage,
        //images_upload_base_path: '/storage',
        images_upload_handler: image_upload_handler,
        file_picker_callback: file_picker_callback,
        codesample_languages: [
            {text: 'HTML/XML', value: 'markup'},
            {text: 'JavaScript', value: 'javascript'},
            {text: 'CSS', value: 'css'},
            {text: 'PHP', value: 'php'},
            {text: 'Ruby', value: 'ruby'},
            {text: 'Python', value: 'python'},
            {text: 'Java', value: 'java'},
            {text: 'C', value: 'c'},
            {text: 'C#', value: 'csharp'},
            {text: 'C++', value: 'cpp'}
        ],
        fontsize_formats: '12px 14px 16px 18px 24px 36px 48px 56px 72px',
        font_formats: '微软雅黑=Microsoft YaHei,Helvetica Neue,PingFang SC,sans-serif;苹果苹方=PingFang SC,Microsoft YaHei,sans-serif;宋体=simsun,serif;仿宋体=FangSong,serif;黑体=SimHei,sans-serif;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Book Antiqua=book antiqua,palatino;',
        default_link_target: "_blank",
        link_assume_external_targets:true,
        //menubar:true,
        //移动端
        mobile: {
          menubar: false,
          plugins: 'autosave lists autolink image media',
          toolbar: 'undo bold italic styles image media'
        },
        // 同步
        setup: function(editor){ 
          editor.on('change',function(){ editor.save(); });
        }
      });
    
    
    var editor = {
      //dir: layui.cache.host + 'addons/', //模块路径
      //Ajax
      json: function(url, data, success, options){
        var that = this, type = typeof data === 'function';
        
        if(type){
          options = success
          success = data;
          data = {};
        }
  
        options = options || {};
  
        return $.ajax({
          type: options.type || 'post',
          dataType: options.dataType || 'json',
          data: data,
          url: url,
          success: function(res){
            if(res.status === 0) {
              success && success(res);
            } else {
              layer.msg(res.msg || res.code, {shift: 6});
              options.error && options.error();
            }
          }, error: function(e){
            layer.msg('请求异常，请重试', {shift: 6});
            options.error && options.error(e);
          }
        });
      }
  
    // 转义了<> ' ""
    //   ,escape: function(html){
    //       return String(html||'').replace(/&(?!#?[a-zA-Z0-9]+;)/g, '&amp;')
    //           .replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/'/g, '&#39;').replace(/"/g, '&quot;');
    //   }

      ,escape: function(html){
        return String(html||'').replace(/&(?!#?[a-zA-Z0-9]+;)/g, '&amp;');
    }
  
        //内容转义
      ,content: function(content){
          var util = editor
          ,item = faces;
  
          //支持的html标签
        //   var html = function(end){
        //       return new RegExp('\\n*\\|\\-'+ (end||'') +'(div|span|button|table|thead|p|th|tbody|tr|td|ul|li|ol|li|dl|dt|dd|h2|h3|h4|h5)([\\s\\S]*?)\\-\\|\\n*', 'g');
        //   };
  
  
          //XSS
          content = util.escape(content||'')
  
              //转义图片
              .replace(/img\[([^\s]+?)\]/g, function(img){
                  return '<div><img src="' + img.replace(/(^img\[)|(\]$)/g, '') + '"></div>';
              })
  
              //转义@
              .replace(/@(\S+)(\s+?|$)/g, '@<a href="javascript:;" class="fly-aite">$1</a>$2')
  
              
  
              //转义脚本
              .replace(/a(\(javascript:)(.+)(;*\))/g, 'a(javascript:layer.msg(\'非法脚本\');)')
  
              //转义链接
              .replace(/a\([\s\S]+?\)\[[\s\S]*?\]/g, function(str){
                  var href = (str.match(/a\(([\s\S]+?)\)\[/)||[])[1];
                  var text = (str.match(/\)\[([\s\S]*?)\]/)||[])[1];
                  if(!href) return str;
                  var rel =  /^(http(s)*:\/\/)\b(?!(\w+\.)*(sentsin.com|layui.com))\b/.test(href.replace(/\s/g, ''));
                  return '<a href="'+ href +'" style="color: red;" target="_blank"'+ (rel ? ' rel="nofollow"' : '') +'>'+ (text||href) +'</a>';
              })
  
              //转义横线
              .replace(/\[hr\]\n*/g, '<hr>')
  
              //转义表格
              .replace(/\[table\]([\s\S]*)\[\/table\]\n*/g, function(str){
                  return str.replace(/\[(thead|th|tbody|tr|td)\]\n*/g, '<$1>')
                      .replace(/\n*\[\/(thead|th|tbody|tr|td)\]\n*/g, '</$1>')
  
                      .replace(/\[table\]\n*/g, '<table class="layui-table">')
                      .replace(/\n*\[\/table\]\n*/g, '</table>');
              })
  
              //转义 div/span
              .replace(/\n*\[(div|span)([\s\S]*?)\]([\s\S]*?)\[\/(div|span)\]\n*/g, function(str){
                  return str.replace(/\[(div|span)([\s\S]*?)\]\n*/g, '<$1 $2>')
                      .replace(/\n*\[\/(div|span)\]\n*/g, '</$1>');
              })
  
              //转义列表
              .replace(/\[ul\]([\s\S]*)\[\/ul\]\n*/g, function(str){
                  return str.replace(/\[li\]\n*/g, '<li>')
                      .replace(/\n*\[\/li\]\n*/g, '</li>')
  
                      .replace(/\[ul\]\n*/g, '<ul>')
                      .replace(/\n*\[\/ul\]\n*/g, '</ul>');
              })
  
              //转义代码
              .replace(/\[pre\]([\s\S]*)\[\/pre\]\n*/g, function(str){
                  return str.replace(/\[pre\]\n*/g, '<pre>')
                      .replace(/\n*\[\/pre\]\n*/g, '</pre>');
              })
  
              //转义引用
              .replace(/\[quote\]([\s\S]*)\[\/quote\]\n*/g, function(str){
                  return str.replace(/\[quote\]\n*/g, '<div class="layui-elem-quote">')
                      .replace(/\n*\[\/quote\]\n*/g, '</div>');
              })
  
              //转义加粗
              .replace(/\[strong\]([\s\S]*)\[\/strong\]\n*/g, function(str){
                return str.replace(/\[strong\]\n*/g,'<b>')
                .replace(/\n*\[\/strong\]\n*/g, '</b>');
              })

              //转义视频
              .replace(/video\(.*?\)\[([^\s]+?)\]/g, function(str){
                  var cover = (str.match(/video\(([\s\S]+?)\)\[/)||[])[1];
                  var video = (str.match(/\)\[([^\s]+?)\]/)||[])[1];
                  cover = cover ? cover : '/static/res/images/video_cover.jpg';
                  return  '<video poster="'+ cover + '" controls crossorigin><source src="'+ video + '" type="video/mp4"></video>';
              })
              //转义音频
              .replace(/audio\[([^\s]+?)\]/g, function(audio){
                  return  '<audio controls><source src="'+ audio.replace(/(^audio\[)|(\]$)/g, '')+ '" type="audio/mp3"></audio>';
              })
  
          return content;
      }

    }; 

  
    exports('editor', editor);
  });
  
  
  