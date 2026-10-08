# 🛠️ 自行開發/魔改的模組

本伺服器為提升遊戲體驗、突破效能瓶頸並深度整合自建服務，自行開發或深度修改了多款專屬模組與修復補丁。

---

## 🚀 自行開發的模組

基本上就是有投注比較多心力開發的，基於特定架構需求進行了極深度客製化的模組。

<div class="media mb-4 p-3 bg-white rounded border shadow-sm">
<img src="/images/mods/neoauth-reloaded.svg" class="mr-3 rounded shadow-sm align-self-start flex-shrink-0" width="64" height="64" alt="NeoAuthReloaded" style="object-fit: contain;">
<div class="media-body">

### NeoAuthReloaded

<div class="bg-light p-3 rounded mb-3 border text-secondary small">
<ul class="list-unstyled mb-0 pl-0">
  <li class="mb-1"><strong>📦 模組定位：</strong>伺服器專屬身分驗證、免密自動登入與安全防護核心</li>
  <li class="mb-1"><strong>⚙️ 適用環境：</strong>NeoForge 1.21.1（純伺服端 Server-side，玩家免裝）</li>
  <li class="mb-1"><strong>🔗 原始碼網址：</strong><a href="https://github.com/chyuaner/Minecraft-NeoAuthReloaded" target="_blank" rel="noopener noreferrer">https://github.com/chyuaner/Minecraft-NeoAuthReloaded</a></li>
  <li><strong>🤝 深度連動：</strong>Mojang Session 驗證、BlueMap 3D 網頁地圖、TAB 玩家列表</li>
</ul>
</div>

**模組簡介**：
本模組雖然最初復刻自 AuthMeReloaded，但為符合伺服器特殊的雙模式與架構需求，已經歷經極大規模的改動與重度客製化，現已成為本伺服器專屬的認證核心模組：

- **動態正版加密握手**：即使在離線模式 (online-mode: false) 下，正版玩家也能經由 Mojang Session 驗證，享受免密碼自動登入的絲滑體驗，同時確保所有玩家存檔統一綁定離線 UUID，無升級正版掉檔風險。
- **未登入雙層立體防護**：透過「底層封包攔截 + 模組事件攔截」雙重防護，徹底修復未登入玩家可能透過原版特性、模組介面（如精妙背包）、按快捷鍵丟棄/交換物品而導致的幽靈物品與非法物資轉移漏洞。
- **狀態機重構與熔斷保護**：重構原子狀態機，徹底解決原版握手卡頓或 7 秒驗證超時導致的封包重複斷線問題。若 Mojang 驗證伺服器異常，也會自動啟動熔斷保護，讓玩家直接順暢地以密碼離線進服。
- **BlueMap 頭像非同步同步**：將正版或外置皮膚站頭像下載獨立於背景執行並寫入 BlueMap 網頁地圖，設計三層快取機制（包含 Hash 指紋），做到零阻塞主執行緒。
- **TAB 模組深度連動**：不需依賴 PAPI，即可在 TAB 列表顯示登入方式、連線線路 (支援 CDN PoP 機房代碼)、IP 協定 (IPv4/IPv6) 等專屬變數。

</div>
</div>

<div class="media mb-4 p-3 bg-white rounded border shadow-sm">
<img src="/images/mods/neoshcmd.svg" class="mr-3 rounded shadow-sm align-self-start flex-shrink-0" width="64" height="64" alt="NeoShCmd" style="object-fit: contain;">
<div class="media-body">

### NeoShCmd (Native Shell Commander)

<div class="bg-light p-3 rounded mb-3 border text-secondary small">
<ul class="list-unstyled mb-0 pl-0">
  <li class="mb-1"><strong>📦 模組定位：</strong>原生作業系統指令橋接、多模式排程與伺服器自動化維運</li>
  <li class="mb-1"><strong>⚙️ 適用環境：</strong>NeoForge 1.21.1（純伺服端 Server-side，管理員專用）</li>
  <li class="mb-1"><strong>🔗 原始碼網址：</strong>暫時還沒上傳（內部私有維護中）</li>
  <li><strong>🛡️ 安全機制：</strong>Brigadier 原生權限樹、危險任務排他鎖、100% 強制審計日誌</li>
</ul>
</div>

**模組簡介**：
一款專為伺服器設計的原生作業系統指令橋接模組：

- **動態註冊根指令**：允許管理員透過純淨的 YAML 設定，將系統 Shell 腳本註冊為遊戲原生指令 (如 `/backup`, `/sysinfo`)，支援原版 Tab 自動補全與無限層級的子指令驗證。
- **ANSI 色彩無失真轉譯**：Shell 輸出的終端機色彩 (包含 256 色/TrueColor) 皆會自動轉換為 Minecraft 原生色碼，直接在遊戲聊天室呈現完美排版。
- **排他鎖定與多模式調度**：支援同步、非同步等待與背景射後不理三種模式。危險任務 (如備份快照) 支援排他鎖定保護，防止連點撞期，並提供 100% 強制紀錄的終端機安全審計日誌。

</div>
</div>

<div class="media mb-4 p-3 bg-white rounded border shadow-sm">
<img src="/images/mods/neocommandalias.svg" class="mr-3 rounded shadow-sm align-self-start flex-shrink-0" width="64" height="64" alt="NeoCommandAlias" style="object-fit: contain;">
<div class="media-body">

### NeoCommandAlias (NeoCmdAlias)

<div class="bg-light p-3 rounded mb-3 border text-secondary small">
<ul class="list-unstyled mb-0 pl-0">
  <li class="mb-1"><strong>📦 模組定位：</strong>純伺服端原生 Brigadier 指令別名嫁接與全域衝突指令封鎖</li>
  <li class="mb-1"><strong>⚙️ 適用環境：</strong>NeoForge 1.21.1（純伺服端 Server-side）</li>
  <li class="mb-1"><strong>🔗 原始碼網址：</strong>暫時還沒上傳（內部私有維護中）</li>
  <li><strong>🔀 常用映射：</strong>`/co` ➜ NeoProtect（支援無序標籤）、`/res` ➜ FLAN 領地系統</li>
</ul>
</div>

**模組簡介**：
極輕量級的純伺服端原生指令別名與封鎖模組，專為平替模組的使用習慣痛點而生：

- **無縫原生指令樹嫁接**：完美繼承目標指令的 Brigadier 參數驗證與 Tab 補全，絕非單純字串轉發。
- **CoreProtect (`/co`) 映射**：將玩家習慣的 `/co i`、`/co rb` 等完整嫁接至 NeoProtect，甚至支援無序標籤 (如 `t:1h r:10 u:Steve`) 的智慧轉換與預設值自動補齊。
- **Residence (`/res`) 映射**：將領地指令完整對應至 FLAN 模組，保留如 `/res auto` (最大化圈地)、`/res set` 等習慣語法。
- **動態檢測與條件啟用**：依賴目標模組存在與否自動動態註冊，亦可利用此功能精準封鎖特定模組搶佔的衝突全域指令。

</div>
</div>

<div class="media mb-4 p-3 bg-white rounded border shadow-sm">
<img src="/images/mods/tab-gamemode.svg" class="mr-3 rounded shadow-sm align-self-start flex-shrink-0" width="64" height="64" alt="tab-gamemode" style="object-fit: contain;">
<div class="media-body">

### tab-gamemode

<div class="bg-light p-3 rounded mb-3 border text-secondary small">
<ul class="list-unstyled mb-0 pl-0">
  <li class="mb-1"><strong>📦 模組定位：</strong>TAB 玩家清單動態狀態橋接與管理員操作廣播抑制</li>
  <li class="mb-1"><strong>⚙️ 適用環境：</strong>NeoForge 1.21.1（純伺服端 Server-side）</li>
  <li class="mb-1"><strong>🔗 原始碼網址：</strong>暫時還沒上傳（內部私有維護中）</li>
  <li><strong>🧩 技術核心：</strong>Sponge Mixin 底層攔截、TAB 自訂 Placeholder 註冊</li>
</ul>
</div>

**模組簡介**：
橋接玩家狀態至 TAB 模組的輕量附屬，並包含原版系統廣播的深度優化：

- **管理員廣播抑制 (Admin Broadcast Suppression)**：透過 Mixin 底層攔截，抑制管理員在使用 `/gamemode` (切換模式) 或 `/tp` (傳送) 等指令時對其他管理員發送洗頻通知，但執行者本人仍會收到操作成功的系統反饋，保持管理員頻道的清爽。
- **自訂 Placeholder 狀態**：自動註冊 `%player_gamemode%` 與 `%player_health_display%` 變數，讓 TAB 列表能動態直觀顯示所有玩家當前的生存/創造模式與血量飽食度狀態。

</div>
</div>

<div class="media mb-4 p-3 bg-white rounded border shadow-sm">
<img src="/images/mods/tgbridge-img2imgur.svg" class="mr-3 rounded shadow-sm align-self-start flex-shrink-0" width="64" height="64" alt="tgbridge-img2imgur" style="object-fit: contain;">
<div class="media-body">

### tgbridge-img2imgur

<div class="bg-light p-3 rounded mb-3 border text-secondary small">
<ul class="list-unstyled mb-0 pl-0">
  <li class="mb-1"><strong>📦 模組定位：</strong>Telegram 圖片與貼圖轉存圖床，橋接遊戲內懸停看圖</li>
  <li class="mb-1"><strong>⚙️ 適用環境：</strong>NeoForge 1.21.1（純伺服端 Server-side）</li>
  <li class="mb-1"><strong>🔗 原始碼網址：</strong><a href="https://github.com/chyuaner/Minecraft-tgbridge-img2imgur" target="_blank" rel="noopener noreferrer">https://github.com/chyuaner/Minecraft-tgbridge-img2imgur</a></li>
  <li><strong>🤝 連動模組：</strong>tgbridge、ChatImage (CICode 規範)</li>
</ul>
</div>

**模組簡介**：
專為 tgbridge 與 Telegram 群組連動開發的圖床橋接模組：

- **即時圖片與貼圖轉發**：自動攔截 Telegram 的照片、WebP/動態貼圖與 GIF 動畫，非同步上傳至免費圖床並轉為 ChatImage 的 `CICode` 格式，讓玩家在遊戲內懸停即可直接看圖。
- **WebP 自動轉碼 PNG**：為解決 ChatImage 模組不支援 WebP 貼圖的問題，上傳前會自動在記憶體內轉碼，同時保留 Alpha 透明通道。
- **雙層永久去重快取**：藉由 Telegram 檔案唯一 ID 建立記憶體與硬碟的持久化去重快取，玩家狂刷重複貼圖時達到 &lt;1ms 即時轉發、0 網路請求，保護圖床不被限流。

</div>
</div>

<div class="media mb-4 p-3 bg-white rounded border shadow-sm">
<img src="/images/mods/essentialsneoforge-by-barian.svg" class="mr-3 rounded shadow-sm align-self-start flex-shrink-0" width="64" height="64" alt="essentialsneoforge-by-barian" style="object-fit: contain;">
<div class="media-body">

### essentialsneoforge-by-barian

<div class="bg-light p-3 rounded mb-3 border text-secondary small">
<ul class="list-unstyled mb-0 pl-0">
  <li class="mb-1"><strong>📦 模組定位：</strong>基礎管理與傳送指令系統，具備視覺化箱子選單與權限管理</li>
  <li class="mb-1"><strong>👤 核心作者：</strong>Barian（伺服器好友）</li>
  <li class="mb-1"><strong>⚙️ 適用環境：</strong>NeoForge 1.21.1</li>
  <li class="mb-1"><strong>🔗 原始碼網址：</strong><a href="https://github.com/Barian0517/essentialsneoforge-by-barian-" target="_blank" rel="noopener noreferrer">https://github.com/Barian0517/essentialsneoforge-by-barian-</a></li>
  <li><strong>✨ 功能亮點：</strong>視覺化箱子 GUI 面板、好友權限系統、界伏盒 Kit 套件</li>
</ul>
</div>

**模組簡介**：
由伺服器好友 Barian 專為生存服開發的基礎工具模組：

- 提供了類似 EssentialsX 的常用管理與生存系統，如 `/home`、`/warp`、`/tpa`、`/rtp` 與 `/back`，並配有冷卻時間與傳送歷程系統。
- **箱子 GUI 介面整合**：提供基於箱子介面的視覺化菜單，方便玩家操作地標或處理傳送請求。
- 包含好友權限系統，以及使用界伏殼盒創建套件 (Kit) 的管理功能。

</div>
</div>

<div class="media mb-4 p-3 bg-white rounded border shadow-sm">
<img src="/images/mods/bluemap-barian-essentials.svg" class="mr-3 rounded shadow-sm align-self-start flex-shrink-0" width="64" height="64" alt="BlueMap-barian-essentials" style="object-fit: contain;">
<div class="media-body">

### BlueMap-barian-essentials

<div class="bg-light p-3 rounded mb-3 border text-secondary small">
<ul class="list-unstyled mb-0 pl-0">
  <li class="mb-1"><strong>📦 模組定位：</strong>BlueMap 3D 衛星地圖之公共地標與家園標記即時同步附屬</li>
  <li class="mb-1"><strong>⚙️ 適用環境：</strong>NeoForge 1.21.1（純伺服端 Server-side）</li>
  <li class="mb-1"><strong>🔗 原始碼網址：</strong><a href="https://github.com/chyuaner/Minecraft-BlueMap-barian-essentials" target="_blank" rel="noopener noreferrer">https://github.com/chyuaner/Minecraft-BlueMap-barian-essentials</a></li>
  <li><strong>🤝 深度連動：</strong>BlueMap、essentialsneoforge-by-barian</li>
</ul>
</div>

**模組簡介**：
為 barian 開發的 essentials 模組量身打造的 BlueMap 網頁地圖連動附屬：

- 能自動並即時地將伺服器公共傳送點 (Warp)、各維度預設出生點 (DimWarp) 與玩家個人的家 (Home) 同步為 BlueMap 上的 3D 地圖標記。
- 支援動態更新、登入/登出事件自動排程刷新，並具有高容錯的記憶體與磁碟資料雙重降級讀取保障。

</div>
</div>

---

## 🔧 魔改後的模組

基於現有開源社群模組進行深度的架構修改、效能演算法重構與漏洞修補，以突破伺服端運行極限或完善特定相容性。

### ⚡ 原 Paper 或 Fabric 復刻與效能優化

<div class="media mb-4 p-3 bg-white rounded border shadow-sm">
<img src="/images/mods/bluemap-marker.png" class="mr-3 rounded shadow-sm align-self-start flex-shrink-0" width="64" height="64" alt="bluemap-marker" style="object-fit: contain;">
<div class="media-body">

### bluemap-marker

<div class="bg-light p-3 rounded mb-3 border text-secondary small">
<ul class="list-unstyled mb-0 pl-0">
  <li class="mb-1"><strong>📦 模組定位：</strong>遊戲內視覺化 GUI 標記建立、自訂與地圖管理模組</li>
  <li class="mb-1"><strong>⚙️ 適用環境：</strong>NeoForge 1.21.1（相容移植運行）</li>
  <li class="mb-1"><strong>🔗 原始碼網址：</strong>暫時還沒上傳（移植版本維護中）</li>
  <li><strong>🏛️ 基礎專案網址：</strong><a href="https://github.com/MiraculixxT/bluemap-marker" target="_blank" rel="noopener noreferrer">https://github.com/MiraculixxT/bluemap-marker</a> (原專案)</li>
</ul>
</div>

**模組簡介**：
基於原版進行復刻修改的 BlueMap 標記模組，讓玩家能透過遊戲內互動 GUI 選單建立、自訂與管理自己在地圖上的專屬標記。本伺服器確保其在 NeoForge 環境下的相容與穩定運行。

</div>
</div>

<div class="media mb-4 p-3 bg-white rounded border shadow-sm">
<img src="/images/mods/fast-tnt-neoforge.svg" class="mr-3 rounded shadow-sm align-self-start flex-shrink-0" width="64" height="64" alt="fast-tnt-neoforge" style="object-fit: contain;">
<div class="media-body">

### fast-tnt-neoforge

<div class="bg-light p-3 rounded mb-3 border text-secondary small">
<ul class="list-unstyled mb-0 pl-0">
  <li class="mb-1"><strong>📦 模組定位：</strong>BFS 波動演算法與 TNT 爆炸射線運算極致優化核心</li>
  <li class="mb-1"><strong>⚙️ 適用環境：</strong>NeoForge 1.21.1（伺服端 Server-side）</li>
  <li class="mb-1"><strong>🔗 原始碼網址：</strong>暫時還沒上傳（內部私有維護中）</li>
  <li><strong>⚡ 效能躍升：</strong>減少 90% 以上 CPU 運算開銷，消除海量 TNT 造成的死鎖與崩潰</li>
</ul>
</div>

**模組簡介**：
針對 Minecraft 原版海量 TNT 引爆導致 TPS 暴跌歸零與 Watchdog 崩潰問題開發的極致效能演算法重構模組：

- **BFS 廣度優先破壞傳播**：將原版單顆 TNT 的 1352 條隨機重疊射線，改為向相鄰方塊擴散的 BFS 波動演算法，每個方塊只被查詢 1 次，大幅減少 90% 以上的 CPU 運算開銷，但 100% 完美保留水、黑曜石等原版防爆機制與掉落物規則。
- **TNT 實體遮擋免算**：直接免除「點燃的 TNT」彼此之間毫無意義的射線傷害與遮擋檢測，解決數千顆 TNT 聚集時產生數億次射線迴圈的卡頓源頭 (玩家與怪物的掩體視線防護依然正常運作)。平時無爆炸時 CPU 負載為 0。

</div>
</div>

---

### 🛡️ 附屬形式的修復與改良

<div class="media mb-4 p-3 bg-white rounded border shadow-sm">
<img src="/images/mods/dragonlib-optimizer-fix.svg" class="mr-3 rounded shadow-sm align-self-start flex-shrink-0" width="64" height="64" alt="dragonlib-optimizer-fix" style="object-fit: contain;">
<div class="media-body">

### dragonlib-optimizer-fix

<div class="bg-light p-3 rounded mb-3 border text-secondary small">
<ul class="list-unstyled mb-0 pl-0">
  <li class="mb-1"><strong>📦 模組定位：</strong>修補 DragonLib 與 ModernFix 動態模型烘焙嚴重凍結卡頓</li>
  <li class="mb-1"><strong>⚙️ 適用環境：</strong>NeoForge 1.21.1（客戶端 / 伺服端）</li>
  <li class="mb-1"><strong>🔗 原始碼網址：</strong>無提供公開原始碼（專用修補補丁）</li>
  <li><strong>🧩 核心原理：</strong>透過 Sponge Mixin 強制攔截全域遍歷空轉，無損恢復流暢幀率</li>
</ul>
</div>

**模組簡介**：
專為修補 DragonLib 與 ModernFix 動態模型烘焙 (Dynamic Model Baking) 嚴重衝突的底層效能修復模組：

- **問題修正**：DragonLib 原本在模型載入事件中會進行無差別的全域遍歷 (動輒幾千幾萬個註冊模型)，導致遊戲在遇到新方塊或開啟 UI 時畫面嚴重凍結卡死。
- **優化方式**：透過 Sponge Mixin 強制攔截該段邏輯並回傳空集合，跳過每次數千次無意義的空轉比對，在完全不影響模組功能的情況下徹底恢復極致流暢的遊戲幀率。

</div>
</div>

<div class="media mb-4 p-3 bg-white rounded border shadow-sm">
<img src="/images/mods/craftyeyeballs-wsmc-compat.svg" class="mr-3 rounded shadow-sm align-self-start flex-shrink-0" width="64" height="64" alt="craftyeyeballs-wsmc-compat" style="object-fit: contain;">
<div class="media-body">

### craftyeyeballs-wsmc-compat

<div class="bg-light p-3 rounded mb-3 border text-secondary small">
<ul class="list-unstyled mb-0 pl-0">
  <li class="mb-1"><strong>📦 模組定位：</strong>CraftyEyeballs、WSMC (WebSocket) 與 ZstdNet 壓縮協定衝突解決方案</li>
  <li class="mb-1"><strong>⚙️ 適用環境：</strong>NeoForge 1.21.1（客戶端底層網絡調度）</li>
  <li class="mb-1"><strong>🔗 原始碼網址：</strong>無提供公開原始碼（內部整合補丁）</li>
  <li><strong>🌐 修復目標：</strong>防止 TCP 錯連 CDN 443 埠產生 HTTP 400，並避開 ZstdNet 協議衝突</li>
</ul>
</div>

**模組簡介**：
為解決 CraftyEyeballs (Happy Eyeballs 連線加速)、WSMC (WebSocket 代理) 與 ZstdNet 壓縮之間複雜的網路協定衝突：

- 攔截底層客戶端連線物件，將 WebSocket 握手資訊無縫注回被 CraftyEyeballs 繞過的原版連線執行緒，避免因 TCP 錯連 CDN 的 443 HTTPS 埠而產生 HTTP 400 錯誤，並自動避開 ZstdNet 的協議衝撞，確保玩家順暢連線。

</div>
</div>

<div class="media mb-4 p-3 bg-white rounded border shadow-sm">
<img src="/images/mods/kogtyv-townyandvillage-fix.svg" class="mr-3 rounded shadow-sm align-self-start flex-shrink-0" width="64" height="64" alt="kogtyv-TownyAndVillage-fix" style="object-fit: contain;">
<div class="media-body">

### kogtyv-TownyAndVillage-fix

<div class="bg-light p-3 rounded mb-3 border text-secondary small">
<ul class="list-unstyled mb-0 pl-0">
  <li class="mb-1"><strong>📦 模組定位：</strong>修復 Kogtyv 模組之原版結構蒸發漏洞、NBT 拼寫錯誤與命名空間污染</li>
  <li class="mb-1"><strong>⚙️ 適用環境：</strong>NeoForge 1.21.1（伺服端 / 世界生成）</li>
  <li class="mb-1"><strong>🔗 原始碼網址：</strong>無提供公開原始碼（高可靠性修復附屬）</li>
  <li><strong>🛠️ 修復亮點：</strong>動態掃描 1000+ 份 Jigsaw NBT、保護原版村莊與沙漠神殿、修復進度洗頻</li>
</ul>
</div>

**模組簡介**：
針對 Kogtyv 模組的毀滅性 Bug 與命名空間污染進行強制修復的高可靠性附屬模組：

- **主動防禦覆寫**：透過底層攔截，阻止原模組將原版村莊與沙漠神殿結構池替換成空檔案，避免原版結構在世界上徹底蒸發。
- **動態補全與命名空間隔離**：全域動態掃描 1000+ 份 Jigsaw NBT 檔案，將作者 36 處筆誤造成的斷頭街道與殘缺大樓單元全數補齊橋接。並將所有新建築獨立移至乾淨的 `kogtyvtav:` 命名空間，杜絕衝突。
- **進度觸發修復**：修復作者拼寫錯誤 (`structures` -> `structure`) 導致玩家一登入便瞬間解鎖所有城鎮進度並嚴重洗頻的重大漏洞。

</div>
</div>

---

### 🧬 直接修改本體

<div class="media mb-4 p-3 bg-white rounded border shadow-sm">
<img src="/images/mods/wsmc.png" class="mr-3 rounded shadow-sm align-self-start flex-shrink-0" width="64" height="64" alt="wsmc" style="object-fit: contain;">
<div class="media-body">

### wsmc

<div class="bg-light p-3 rounded mb-3 border text-secondary small">
<ul class="list-unstyled mb-0 pl-0">
  <li class="mb-1"><strong>📦 模組定位：</strong>Minecraft WebSocket 代理、CDN DDoS 隱藏防護與大型分片聚合</li>
  <li class="mb-1"><strong>⚙️ 適用環境：</strong>NeoForge 1.21.1 / Fabric（伺服端與客戶端相容）</li>
  <li class="mb-1"><strong>🔗 原始碼網址：</strong><a href="https://github.com/chyuaner/wsmc" target="_blank" rel="noopener noreferrer">https://github.com/chyuaner/wsmc</a> (本伺服器 Fork 版本)</li>
  <li><strong>🏛️ 基礎專案網址：</strong><a href="https://github.com/rikka0w0/wsmc" target="_blank" rel="noopener noreferrer">https://github.com/rikka0w0/wsmc</a> (原專案)</li>
</ul>
</div>

**模組簡介**：
允許 Minecraft 走 WebSocket 協定，使伺服器能隱藏在 CDN (如 Cloudflare) 後方防禦 DDoS。本 Fork 版本特別針對 NeoForge 與大型模組包進行了深度修改：

- **大型封包分片聚合處理**：加入 `WebSocketFrameAggregator` 並大幅提升 Payload 預設上限至 64MB，解決大型模組包在連線同步註冊表時常發生的 `Frame length exceeded` 斷線問題。
- **修復 Legacy Ping 嗅探相容性**：優化伺服端連線嗅探邏輯，修復 MCSManager 等面板使用 2-byte 舊版 Ping 時陷入死鎖無法取得伺服器線上人數的問題。
- **修復 NeoForge 獨立執行環境**：補回原版上游遺漏的 `netty-codec-http` jarJar 依賴打包設定，避免伺服器載入時拋出類別找不到而崩潰。

</div>
</div>

<div class="media mb-4 p-3 bg-white rounded border shadow-sm">
<img src="/images/mods/c6c-fix-330.svg" class="mr-3 rounded shadow-sm align-self-start flex-shrink-0" width="64" height="64" alt="c6c-1.2.6.0-fix-330" style="object-fit: contain;">
<div class="media-body">

### c6c-1.2.6.0-fix-330

<div class="bg-light p-3 rounded mb-3 border text-secondary small">
<ul class="list-unstyled mb-0 pl-0">
  <li class="mb-1"><strong>📦 模組定位：</strong>核心底層邏輯字節碼修補、報錯崩潰阻斷與重新打包</li>
  <li class="mb-1"><strong>⚙️ 適用環境：</strong>NeoForge 1.21.1（伺服端 Server-side）</li>
  <li class="mb-1"><strong>🔗 原始碼網址：</strong>無提供公開原始碼（維持伺服器營運穩定）</li>
  <li><strong>🛡️ 修補技術：</strong>Java Bytecode 底層反組譯熱修復，修補 `fix-330` 重大漏洞</li>
</ul>
</div>

**模組簡介**：
針對特定版本 (1.2.6.0) 導致伺服器報錯的 `fix-330` 重大漏洞，透過直接反組譯技術進行了底層邏輯的強制修補與重新打包，以維持伺服器營運穩定 (未公開原始碼)。

</div>
</div>
