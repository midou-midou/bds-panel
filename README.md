# bds-panel

### MCE小组Minecraft的web开服器
## PS. 此项目已经不再维护，重构的计划已经提上日程。如果你也由此想法，可以[Email](mailto:huzi19980410@gamil.com)给我

--- 
## 部署

1. 安装 `lnmp` 环境或 `lamp` 环境
2. php项目的部署[参考WordPress的部署方法](https://codex.wordpress.org/zh-cn:%E5%AE%89%E8%A3%85WordPress)(可以忽略数据库的部署步骤)
3. 启动停止等控制服务器的脚本 (script文件夹中的sh，**不要随意替换成其他目录结构**) 需要放到 `/home/mcpe`下，需要建立 `mcpe` 的home目录及用户
4. 官网下载的Linux服务器版本需要放到`/home/mcpe/script/server-bedrock-template`目录下(和服务器包中的`bedrock_server`二进制文件同级目录)

## 最后
项目来源于[此项目](https://github.com/smilkobuta/minecraftserverpanel)，当时在他的基础上添加了minecraft query可以查看服务器的状态（在线人数，服务器是否在线，版本等）  

**此项目中直接是在部署机器上启停服务，是不安全的，部署请三思。**