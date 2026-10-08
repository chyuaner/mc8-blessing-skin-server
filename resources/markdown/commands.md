# 🎮 伺服器常用指令與操作手冊

歡迎查閱 **MC8 Minecraft 重度機械症 鐵路世界** 官方指令手冊！本伺服器運行於 **NeoForge 1.21.1**，整合了多項便捷模組，包括帳號連動系統、Essentials 快捷傳送、Flan 領地保護與 BlueMap 3D 衛星地圖。

---

## 🔐 帳號與登入驗證（NeoAuth Reloaded）

> 💡 **帳號共用說明**  
> 本伺服器採用 **NeoAuth** 資料庫整合，**本官方網站註冊之帳號密碼與遊戲伺服器完全連動**。  
> 只要在官網註冊完成，進入遊戲後直接輸入 `/login <密碼>` 即可登入，無須重新註冊！

| 指令語法                                                                                                                                                                     | 說明                             | 範例與備註                        |
| :--------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | :------------------------------- | :-------------------------------- |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/login " data-tooltip="點選複製指令"><code>/login &lt;密碼&gt;</code></a>                                    | 登入伺服器帳號                   | `/login MyPassword123`            |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/register " data-tooltip="點選複製指令"><code>/register &lt;密碼&gt; &lt;確認密碼&gt;</code></a>             | 遊戲內直接註冊（若未在網頁註冊） | `/register 123456 123456`         |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/changepassword " data-tooltip="點選複製指令"><code>/changepassword &lt;舊密碼&gt; &lt;新密碼&gt;</code></a> | 變更登入密碼                     | `/changepassword oldPass newPass` |

---

## 🧭 基礎操作與傳送系統（Essentials Forge）

伺服器配備完整的 Essentials 傳送與操作系統，多數指令皆支援直覺的 **箱子視覺化介面 (Chest GUI)**。

### 📋 快捷面板與好友

| 指令語法                                                                                                                                             | 說明                                                                       |
| :--------------------------------------------------------------------------------------------------------------------------------------------------- | :------------------------------------------------------------------------- |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/essentials" data-tooltip="點選複製指令"><code>/essentials</code></a>                | **開啟 Essentials 綜合快捷操作面板 (GUI)**，可滑鼠點選操作傳送、地標等功能 |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/addfriend " data-tooltip="點選複製指令"><code>/addfriend &lt;玩家 ID&gt;</code></a> | 將指定玩家加入好友名單，便於日後快速傳送與交流                             |

### 🏠 家園與傳送

| 指令語法                                                                                                                                | 說明                                           | 備註                                             |
| :-------------------------------------------------------------------------------------------------------------------------------------- | :--------------------------------------------- | :----------------------------------------------- |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/sethome " data-tooltip="點選複製指令"><code>/sethome [名稱]</code></a> | 將當前站立位置設為家園                         | 預設名稱為 `home`                                |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/home " data-tooltip="點選複製指令"><code>/home [名稱]</code></a>       | 快速傳送回家園                                 | 輸入 `/home` 可開啟家園選擇選單                  |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/delhome " data-tooltip="點選複製指令"><code>/delhome [名稱]</code></a> | 刪除指定的家園位置                             | `/delhome home`                                  |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/warp " data-tooltip="點選複製指令"><code>/warp [地標名稱]</code></a>   | 傳送至公共地標（如主城、鐵路樞紐站、公共市場） | 輸入 `/warp` 可開啟公共站點列表                  |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/back" data-tooltip="點選複製指令"><code>/back</code></a>               | 返回上一次傳送前的位置或死亡地點               | 冷卻時間 5 秒                                    |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/rtp" data-tooltip="點選複製指令"><code>/rtp</code></a>                 | **隨機荒野傳送**                               | 隨機傳送至 500 ~ 25,000 格外的荒野，方便新手拓荒 |

### 👥 玩家互傳 (TPA)

| 指令語法                                                                                                                                       | 說明                               |
| :--------------------------------------------------------------------------------------------------------------------------------------------- | :--------------------------------- |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/tpa " data-tooltip="點選複製指令"><code>/tpa &lt;玩家 ID&gt;</code></a>       | 請求傳送至該玩家身旁（需對方同意） |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/tphere " data-tooltip="點選複製指令"><code>/tphere &lt;玩家 ID&gt;</code></a> | 請求該玩家傳送至自己身旁           |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/tpaccept" data-tooltip="點選複製指令"><code>/tpaccept</code></a>              | 同意接受對方的傳送請求             |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/tpdeny" data-tooltip="點選複製指令"><code>/tpdeny</code></a>                  | 拒絕對方的傳送請求                 |

---

## 🛡️ 領地與家園保護（Flan 領地模組）

伺服器採用 **Flan (Claims Mod)** 提供強大的防爆、防偷與領地劃設保護，保障玩家的建築與機械設施。

### 🪓 圈地基本流程

1. 手持 **金鋤頭 (Golden Hoe)**。
2. 對著想保護的第一個角落方塊 **點擊右鍵** 設定起點。
3. 對著對角線的第二個角落方塊 **點擊右鍵** 設定終點。
4. 輸入 `/flan claim` 即可完成圈地！

### 📜 常用領地指令

| 指令語法                                                                                                                                                   | 說明                                                               |
| :--------------------------------------------------------------------------------------------------------------------------------------------------------- | :----------------------------------------------------------------- |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/flan menu" data-tooltip="點選複製指令"><code>/flan menu</code></a>                        | **開啟領地視覺化管理面板**（可調整訪問權限、TNT 開關、訪客互動等） |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/flan claim" data-tooltip="點選複製指令"><code>/flan claim</code></a>                      | 確認註冊並購買當前已框選的領地                                     |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/flan trust " data-tooltip="點選複製指令"><code>/flan trust &lt;玩家 ID&gt;</code></a>     | 給予好友該領地的完全存取與建築信任權限                             |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/flan untrust " data-tooltip="點選複製指令"><code>/flan untrust &lt;玩家 ID&gt;</code></a> | 撤銷指定玩家在該領地的信任權限                                     |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/flan list" data-tooltip="點選複製指令"><code>/flan list</code></a>                        | 列出自己目前擁有的所有領地清單與座標                               |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/flan delete" data-tooltip="點選複製指令"><code>/flan delete</code></a>                    | 刪除目前所在位置的領地（釋放領地格數）                             |

---

## 🗺️ 即時地圖與地標標記（BlueMap & BMM）

伺服器搭載 **BlueMap** 3D 即時網頁衛星地圖與 **BMM (BlueMap Marker Manager)** 標記模組。

- **線上衛星地圖**：[https://mc8-map.yuaner.tw](https://mc8-map.yuaner.tw)
- **鐵路路網全景圖**：[https://mc8-track.yuaner.tw](https://mc8-track.yuaner.tw)

| 指令語法                                                                                                                | 說明                                                                       |
| :---------------------------------------------------------------------------------------------------------------------- | :------------------------------------------------------------------------- |
| <a href="javascript:void(0);" class="copy-text-link" data-copy="/bmm" data-tooltip="點選複製指令"><code>/bmm</code></a> | 開啟地圖標記管理選單，可在 3D 網頁地圖上建立您的個人車站、城鎮或景點地標！ |

---

> 💬 若在指令使用或遊玩過程中遇到任何疑問，歡迎隨時造訪 [網頁版遊戲聊天室](https://mc8-chat.yuaner.tw) 或加入我們的 [Telegram 討論群](https://t.me/yuaner_mc8) 尋求管理員或在線玩家的協助！
