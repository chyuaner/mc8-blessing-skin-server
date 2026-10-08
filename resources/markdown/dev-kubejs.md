# 📜 伺服器專屬 KubeJS 魔改腳本

本伺服器為提升鐵道交通安全、強化生存沉浸體驗並突破伺服端效能瓶頸，運用 KubeJS 6 (NeoForge 1.21.1) 深度客製化了多套專屬伺服端腳本與機制調優。

---

## 🚂 鐵道交通與月台安全防護

專為機械動力 (Create) 與 Steam 'n' Rails 鐵路系統設計，結合幾何空間運算法與動態物理力場，在確保列車營運流暢的同時守護生物安全並維持極致效能。

<div class="media mb-4 p-3 bg-white rounded border shadow-sm">
<img src="/images/kubejs/railway-spawning.svg" class="mr-3 rounded shadow-sm align-self-start flex-shrink-0" width="64" height="64" alt="Railway Mob Spawning Prevention" style="object-fit: contain;">
<div class="media-body">

### railway_spawning (機械動力鐵路沿線防生怪腳本)

<div class="bg-light p-3 rounded mb-3 border text-secondary small">
<ul class="list-unstyled mb-0 pl-0">
  <li class="mb-1"><strong>📦 腳本定位：</strong>機械動力與 Steam 'n' Rails 鐵路沿線生物自然生成動態抑制系統</li>
  <li class="mb-1"><strong>⚙️ 適用環境：</strong>KubeJS 6 / NeoForge 1.21.1（純伺服端 Server Script，玩家免裝）</li>
  <li class="mb-1"><strong>📁 腳本檔案：</strong><code>server_scripts/railway_spawning.js</code></li>
  <li><strong>🤝 深度連動：</strong>Create、Create: Steam 'n' Rails、Living Things、Alex's Mobs</li>
</ul>
</div>

**腳本簡介**：
本腳本針對機械動力鐵路沿線頻繁自然生成牲畜與村民、導致火車高速撞擊造成慘劇與掉檔風險而設計的高效能防護系統：

- **鐵道限界立體防生怪圈**：精準偵測列車軌道（包含 Create 原生軌道、Steam 'n' Rails 各類延伸軌道、單軌與道岔），在軌道中心水平 5 格（左右各 2 格）、垂直 7 格的立體限界內阻止目標生物自然生成。
- **排除原版鐵路與自動化農場**：明確排除原版鐵軌 (`minecraft:rails`)，確保玩家的原版紅石鐵路、生怪塔、村民運輸與各類原版自動化農場 100% 不受干擾。
- **階層式目標生物過濾**：
  - **村民與流浪商人**：預設全面保護，徹底根除鐵路隨機生村民被撞死的心痛慘劇。
  - **一般被動動物**：支援 `category:creature` 與 `livingthings:*` 通配符一鍵攔截，防止牛、羊、豬、大象等大型動物在鐵軌遊蕩擋道。
  - **敵對怪物自由放行**：預設不封鎖怪物生成，保留玩家駕駛武裝列車高速衝撞怪物的爽快戰鬥體驗。
- **極致效能優化架構**：
  - **空氣方塊 0 秒快篩 (Fast-Path Skip)**：檢測時直接跳過 70% 以上的空氣方塊，瞬間降低運算負載。
  - **方塊 ID 記憶體快取 (Block ID Cache)**：所有方塊標籤與前綴判定僅計算一次，後續達到 $O(1)$ 瞬間命中。
  - **純自然生成過濾**：僅攔截 `NATURAL` 自然隨機刷怪，不干涉生怪磚、刷怪蛋、村民繁殖或管理員指令生成。
  - **IIFE 私有作用域隔離**：全邏輯閉包封裝，杜絕全域變數污染與記憶體洩漏。

</div>
</div>

<div class="media mb-4 p-3 bg-white rounded border shadow-sm">
<img src="/images/kubejs/station-safety.svg" class="mr-3 rounded shadow-sm align-self-start flex-shrink-0" width="64" height="64" alt="Station Platform Safety & Boarding" style="object-fit: contain;">
<div class="media-body">

### station_safety (車站月台安全防護與自動登車腳本)

<div class="bg-light p-3 rounded mb-3 border text-secondary small">
<ul class="list-unstyled mb-0 pl-0">
  <li class="mb-1"><strong>📦 腳本定位：</strong>智慧型車站月台防墜排斥力場、深槽跌落救援與列車靠站登車偵測核心</li>
  <li class="mb-1"><strong>⚙️ 適用環境：</strong>KubeJS 6 / NeoForge 1.21.1（純伺服端 Server Script，玩家免裝）</li>
  <li class="mb-1"><strong>📁 腳本檔案：</strong><code>server_scripts/station_safety.js</code></li>
  <li><strong>🤝 深度連動：</strong>Create、Create: Steam 'n' Rails、Guard Villagers、Living Things</li>
</ul>
</div>

**腳本簡介**：
專為解決 Minecraft 生物 AI 在車站月台容易失足踩空、跌入軌道深槽被進站列車撞死或沿著鐵軌長度被反覆彈飛卡死而設計的尖端安全系統：

- **月台邊緣即時防墜推回**：受保護目標（村民、警衛村民、家畜、寵物伴侶等）在月台邊緣踏入危險限界時，系統會智慧計算距離最近的軌道軸向，施加定向推力柔性彈回月台內部，杜絕失足跌落。
- **深槽防死鎖跌落救援**：若生物不慎跌入低於月台的 2 格高鐵軌深槽（腳底低於軌道高度 +0.8 格），立即強制安全傳送回月台表面；內建 3 秒內連續觸發監控與受傷反轉反彈機制的防死鎖保護，徹底解決卡在月台側壁的死循環。
- **列車到站智慧放行 (Boarding Clearance)**：透過列車旋轉矩陣 (Contraption Matrix) 與軸對齊包圍盒 (AABB) 嚴格檢測。當列車進站靠妥時，自動動態解除排斥力場，允許村民與動物順暢登車！
- **乘車狀態與高速運動免疫**：已乘車實體 (`isPassenger`) 或隨車高速運動實體 (`speedSq > 0.08`) 瞬間豁免放行，徹底消除列車駕駛時的物理推擠衝突與客戶端卡頓。
- **多級空間休眠與算力收斂**：
  - **動態空間休眠 (Spatial Sleep)**：遠離軌道的安全實體自動休眠 30 Tick (1.5 秒)，近軌實體高頻監控，杜絕原野生態無謂計算。
  - **圓形剪裁與垂直速斷**：方塊空間檢測剔除無效四角，垂直列命中一層即停止，方塊檢測量驟降 75%。
  - **單次維度實體快篩**：一鍵同時收集載具與候選目標，空維度或無目標時 0 開銷直接返回。
  - **區域化特效封包**：提示音效與雲霧粒子僅向半徑 16 格內玩家發送，避免全服廣播封包擁塞。

</div>
</div>

---

## 🌾 生存互動與生活品質

著眼於生存日常細節，豐富玩家與生物之間的沉浸式互動，賦予各類食材與模組料理更實用的價值。

<div class="media mb-4 p-3 bg-white rounded border shadow-sm">
<img src="/images/kubejs/villager-feed-heal.svg" class="mr-3 rounded shadow-sm align-self-start flex-shrink-0" width="64" height="64" alt="Villager Feed & Heal System" style="object-fit: contain;">
<div class="media-body">

### villager_feed_heal (村民餵食照護與動態回血系統)

<div class="bg-light p-3 rounded mb-3 border text-secondary small">
<ul class="list-unstyled mb-0 pl-0">
  <li class="mb-1"><strong>📦 腳本定位：</strong>生存服沉浸式互動補血機制，支援動態食物營養計算、料理獎勵與容器返還</li>
  <li class="mb-1"><strong>⚙️ 適用環境：</strong>KubeJS 6 / NeoForge 1.21.1（純伺服端 Server Script，玩家免裝）</li>
  <li class="mb-1"><strong>📁 腳本檔案：</strong><code>server_scripts/villager_feed_heal.js</code></li>
  <li><strong>🤝 深度連動：</strong>Minecraft 1.21 DataComponents (FOOD)、農夫樂事 (Farmer's Delight) 等模組料理</li>
</ul>
</div>

**腳本簡介**：
改善原版生存中村民受傷無法直接治療（只能依賴昂貴且危險的噴濺治療藥水）的痛點，打造如同寵物照護般的溫馨右鍵餵食治癒機制：

- **右鍵餵食直覺互動**：當村民受傷未滿血時，玩家手持任意食物右鍵點擊村民即可進行餵食療傷；村民滿血時自動退讓，正常開啟原版交易選單，操作自然不衝突。
- **1.21 DataComponents 動態營養換算**：深度對接 Minecraft 1.21 底層 `DataComponents.FOOD` 規範，根據食物的營養值 (Nutrition) 自動動態折算回血量（基礎係數 0.8，設有單次回血限制 1~12 點平衡上限）。
- **精緻料理加成與生食懲罰**：
  - **精緻料理加成**：碗裝料理、燉菜、湯麵類精緻飲食給予額外 `+2` 點回血獎勵，提升烹飪料理的實用意願。
  - **生食折扣**：生肉、生魚類原料效果減半 (`0.5x`)，符合常理邏輯。
- **特殊食物手動覆寫與毒物黑名單**：
  - **特殊補品**：附魔金蘋果、金蘋果直接回滿血 (20 點)；金胡蘿蔔回 8 點；餅乾與各類漿果回 1 點。
  - **嚴格毒物防呆**：腐肉、毒馬鈴薯、河豚、蜘蛛眼等有害食物全面列入黑名單禁止餵食，防止誤傷村民。
- **容器返還與沉浸式視覺回饋**：
  - **自動返還空碗**：非創造模式消耗食物時，若食用湯麵、燉菜會自動返還空碗給玩家，完全不浪費容器資源。
  - **音效與愛心粒子**：餵食成功時播放進食咀嚼音效，並在村民頭頂散發開心愛心粒子 (`happy_villager`)，回饋感十足。

</div>
</div>
