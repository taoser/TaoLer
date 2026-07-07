/**
 * @module storage
 * @desc layui本地存储工具，封装 localStorage / sessionStorage
 * @feature
 * 1. 统一序列化/反序列化JSON，无需手动处理
 * 2. set支持合并更新对象字段，不覆盖整条数据
 * 3. remove支持删除key内指定字段，或直接删除整条key
 * 4. 区分普通对象与数组，数组不支持合并/局部删除
 * 5. 全方法try-catch捕获异常，仅控制台打印错误，无UI弹窗依赖
 * 6. 内置clear清空全部存储方法
 */
/*
// ========== localStorage 持久存储示例 ==========
    // 完整写入对象
    storage.local.set('user', { id: 1, name: '测试', token: 'xxx123' });
    // 合并局部更新（只修改name，其他字段保留）
    storage.local.set('user', { name: '新名字' }, true);
    // 获取数据
    const userInfo = storage.local.get('user');
    console.log(userInfo);
    // 删除对象内单个字段token
    storage.local.remove('user', 'token');
    // 删除整条user数据
    storage.local.remove('user');
    // 清空全部localStorage
    // storage.local.clear();

    // ========== sessionStorage 会话存储示例 ==========
    storage.session.set('tempData', { page: 1, size: 10 });
    storage.session.set('tempData', { page: 2 }, true);
    console.log(storage.session.get('tempData'));
    storage.session.remove('tempData', 'size');
*/

layui.define(function (exports) {
    /**
     * 生成存储操作实例（localStorage / sessionStorage 复用一套逻辑）
     * @param {Storage} storageObj window.localStorage / window.sessionStorage
     * @returns {Object} 统一操作api
     */
    function createStorageInstance(storageObj) {
        return {
            /**
             * 获取存储值
             * @param {string} name 存储键名
             * @returns {any} 解析后数据，无数据/解析失败返回null
             */
            get: function (name) {
                if (typeof name !== 'string' || name.trim() === '') {
                    console.warn('storage.get：键名不能为空');
                    return null;
                }
                try {
                    const raw = storageObj.getItem(name);
                    // 无存储数据直接返回null
                    if (!raw) return null;
                    return JSON.parse(raw);
                } catch (err) {
                    console.error(`storage.get[${name}] 数据解析失败：`, err);
                    return null;
                }
            },

            /**
             * 设置存储值
             * @param {string} name 存储键名
             * @param {any} data 需要存入的数据
             * @param {boolean} [isMerge=false] 是否合并更新对象
             *        true：仅合并传入字段，保留原有对象其他属性（仅普通object生效，数组无效）
             *        false：直接覆盖整条数据
             */
            set: function (name, data, isMerge = false) {
                if (typeof name !== 'string' || name.trim() === '') {
                    console.warn('storage.set：键名不能为空');
                    return;
                }
                if (data === undefined) {
                    console.warn(`storage.set[${name}]：待存储数据不能为undefined`);
                    return;
                }

                try {
                    let saveData = data;
                    // 开启合并更新，且数据为普通对象（排除数组）
                    if (isMerge && typeof data === 'object' && data !== null && !Array.isArray(data)) {
                        const oldData = this.get(name);
                        // 原有数据也是普通对象才执行合并
                        if (oldData && typeof oldData === 'object' && oldData !== null && !Array.isArray(oldData)) {
                            saveData = Object.assign({}, oldData, data);
                        }
                    }
                    storageObj.setItem(name, JSON.stringify(saveData));
                } catch (err) {
                    console.error(`storage.set[${name}] 写入存储失败：`, err);
                }
            },

            /**
             * 删除存储
             * @param {string} name 存储键名
             * @param {string} [field] 对象内部字段名；不传则删除整条key
             *        仅普通object支持局部删除，数组无效
             */
            remove: function (name, field) {
                if (typeof name !== 'string' || name.trim() === '') {
                    console.warn('storage.remove：键名不能为空');
                    return;
                }

                try {
                    // 未传字段，直接删除整条key
                    if (field === undefined || field === null) {
                        storageObj.removeItem(name);
                        return;
                    }

                    // 传入字段，仅删除对象内指定属性
                    const data = this.get(name);
                    // 判断是否为普通对象
                    if (data && typeof data === 'object' && data !== null && !Array.isArray(data)) {
                        delete data[field];
                        this.set(name, data);
                    } else {
                        console.warn(`storage.remove[${name}]：存储数据非普通对象，无法删除内部字段`);
                    }
                } catch (err) {
                    console.error(`storage.remove[${name}] 删除失败：`, err);
                }
            },

            /**
             * 清空当前存储下所有数据
             */
            clear: function () {
                try {
                    storageObj.clear();
                } catch (err) {
                    console.error('storage.clear 清空存储失败：', err);
                }
            }
        };
    }

    // 导出模块，区分本地持久存储 / 会话临时存储
    const storage = {
        /** 持久存储 localStorage，关闭浏览器后数据仍保留 */
        local: createStorageInstance(localStorage),
        /** 会话存储 sessionStorage，关闭当前标签页数据自动清除 */
        session: createStorageInstance(sessionStorage)
    };

    // layui输出模块，模块名称storage
    exports('storage', storage);
});