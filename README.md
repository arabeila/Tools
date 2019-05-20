# Tools

## 江苏阿拉贝拉信息技术有限公司

### 常用函数库 & 模版发布助手



#### 模版发布助手

	目录
		命令集合
		配置解析


##### 命令集合

1. **发布控制器模板**

      `php artisan create:controller {name} {-m|--model=} {--api} {--parent}`

| 参数        | 说明                           | 示例                    |
| ----------- | ------------------------------ | ----------------------- |
| name        | 控制器名称                     | Admin\ProductController |
| -m\|--model | 模型名称                       | Models\Product          |
| --api       | 去除模板内 create 和 edit 方法 |                         |
| --parent    | 占位参数,无实际作用            |                         |

2. **发布请求类模板**
      `php artisan create:request {name}`


| 参数 | 说明       | 示例           |
| ---- | ---------- | -------------- |
| name | 请求类名称 | ProductRequest |

3. **发布视图模板**

      `php artisan create:template {path} {-m|--model=} {--api} {--vue}`


| 参数        | 说明                                     | 示例               |
| ----------- | ---------------------------------------- | ------------------ |
| path        | 相对路径(参考配置文件)                   | Admin\|Admin\pages |
| -m\|--model | 模型名称                                 | Models\Product     |
| --api       | 是否创建 create 和 edit 文件模板         |                    |
| --vue       | 默认创建 blade 模板 ,开启后创建 vue 模板 |                    |

##### 配置解析

​	文件 tools.php

| 参数      | 说明     | 默认                 |
| --------- | -------- | -------------------- |
| path.vue  | vue 模板发布根目录 | resources\assets\js |
| path.view | blade 模板发布根目录 | resources\views     |

