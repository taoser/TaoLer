// import axios from './axios.min.js';

    const instance = axios.create({
        //baseURL: 'https://some-domain.com/api/',
        timeout: 2000
    });

    // 添加请求拦截器
    instance.interceptors.request.use(
        // 在发送请求之前做些什么
        config => {
            const token = localStorage.getItem('tao-user');
            if(token) {
                config.headers['Authorization'] = `Bearer ${token}`;
            }
            return config;
        },
         error => {
            // 对请求错误做些什么
            return Promise.reject(error);
        }
    ),

    // 添加响应拦截器
    instance.interceptors.response.use(function (response) {
        // 2xx 范围内的状态码都会触发该函数。
        // 对响应数据做点什么
        return response.data;
    }, function (error) {
        //console.log(error)
        // 超出 2xx 范围的状态码都会触发该函数。
        if(error.response) {
            const status = error.response.status;
            layui.layer.closeAll();
            if(status === 401) {
                // 未授权， 例如token过期等
                // 可以进行重新登录
                layui.layer.msg('401未授权', {icon: 0});
            } else if(status === 403) {
                // 禁止访问，可能权限不足
                // console.log(403);
                layui.layer.msg('403禁止访问', {icon: 4});
            } else if(status === 404) {
                // 资源未找到
                // console.log(404);
                layui.layer.msg('404资源未找到', {icon: 2});
            } else {
                // 其它错误
                layui.layer.msg('未知错误');
            }
        } else if(error.request) {
            // 请求发出去没有响应
            // console.log('没有响应');
            layui.layer.msg('服务器无相应', {icon: 3});
        } else {
            // 其它如网络错误
            // console.log('发生了其它错误', error.message);
            layui.layer.msg('网络错误', {icon: 5});
        }
        
        // 对响应错误做点什么
        return Promise.reject(error);
    })

    const http = {
        get:(url,params = {}) => instance.get(url, {params}),
        post:(url, data = {}) => instance.post(url,data),
        put:(url, data = {}) =>instance.put(url, data),
        delete:(url) => instance.delete(url)
    }

    // export default http;