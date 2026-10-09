/* ==============================================================================
   MC8 公開下載站客戶端解析器 (Zero Backend Overhead)
   ============================================================================== */

;(function () {
  if (document.body) {
    document.body.classList.add('dl-body', 'hold-transition', 'layout-top-nav')
  }

  // 1. 動態更新麵包屑導航 (Breadcrumbs)
  function setupBreadcrumbs() {
    var breadcrumbs = document.getElementById('dl-breadcrumbs')
    if (!breadcrumbs) return

    var path = window.location.pathname
    var segments = path.split('/').filter(Boolean)
    breadcrumbs.innerHTML = ''

    // 尋找 dl 在路徑中的索引
    var dlIndex = segments.indexOf('dl')
    var rootLi = document.createElement('li')

    if (dlIndex === -1) {
      rootLi.innerHTML =
        '<a href="/dl/"><i class="fas fa-server mr-1"></i>下載根目錄 (/dl)</a>'
      breadcrumbs.appendChild(rootLi)
    } else {
      var currentHref = '/' + segments.slice(0, dlIndex + 1).join('/') + '/'
      if (segments.length === dlIndex + 1) {
        rootLi.className = 'active'
        rootLi.innerHTML =
          '<i class="fas fa-server mr-1 text-info"></i>下載根目錄 (/dl)'
        breadcrumbs.appendChild(rootLi)
        return
      } else {
        rootLi.innerHTML =
          '<a href="' +
          currentHref +
          '"><i class="fas fa-server mr-1"></i>下載根目錄 (/dl)</a>'
        breadcrumbs.appendChild(rootLi)
      }

      // 處理後續子目錄層級
      for (var i = dlIndex + 1; i < segments.length; i++) {
        var seg = decodeURIComponent(segments[i])
        currentHref += encodeURIComponent(seg) + '/'
        var li = document.createElement('li')

        if (i === segments.length - 1) {
          li.className = 'active'
          li.innerHTML =
            '<i class="fas fa-folder-open mr-1 text-warning"></i>' +
            escapeHtml(seg)
        } else {
          li.innerHTML =
            '<a href="' +
            currentHref +
            '"><i class="fas fa-folder mr-1"></i>' +
            escapeHtml(seg) +
            '</a>'
        }
        breadcrumbs.appendChild(li)
      }
    }
  }

  // 2. 檔案圖示與標籤對應表
  function getFileMeta(name, isDir, isParent) {
    if (isParent) {
      return {
        icon: 'fa-arrow-turn-up text-primary',
        badge: '',
        color: '#0284c7',
      }
    }
    if (isDir) {
      return { icon: 'fa-folder text-warning', badge: '目錄', color: '#f59e0b' }
    }

    var ext = name.split('.').pop().toLowerCase()
    switch (ext) {
      // Minecraft & 模組
      case 'jar':
        return { icon: 'fa-cube text-info', badge: 'MOD/JAR' }
      case 'litematic':
      case 'schem':
      case 'schematic':
        return { icon: 'fa-cubes text-purple', badge: '投影檔' }
      case 'mcaddon':
      case 'mcpack':
      case 'mcworld':
        return { icon: 'fa-box-archive text-success', badge: '資源包' }

      // 壓縮整合包
      case 'zip':
      case '7z':
      case 'rar':
      case 'tar':
      case 'gz':
      case 'xz':
        return { icon: 'fa-file-zipper text-danger', badge: '壓縮包' }

      // 執行檔與腳本
      case 'exe':
      case 'msi':
      case 'bat':
      case 'cmd':
      case 'sh':
      case 'ps1':
        return { icon: 'fa-terminal text-danger', badge: '程式' }

      // 設定與數據
      case 'json':
      case 'toml':
      case 'cfg':
      case 'yaml':
      case 'yml':
      case 'conf':
      case 'ini':
      case 'properties':
        return { icon: 'fa-sliders text-info', badge: '設定' }

      // 文本
      case 'txt':
      case 'log':
      case 'md':
        return { icon: 'fa-file-lines text-muted', badge: '文本' }

      // 圖片
      case 'png':
      case 'jpg':
      case 'jpeg':
      case 'webp':
      case 'gif':
      case 'svg':
        return { icon: 'fa-file-image text-success', badge: '圖片' }

      // 文件
      case 'pdf':
        return { icon: 'fa-file-pdf text-danger', badge: 'PDF' }

      default:
        return { icon: 'fa-file text-muted', badge: ext.toUpperCase() }
    }
  }

  // 3. 解析 Nginx autoindex 的 pre 標籤內容
  var allItems = []
  function parseNginxAutoindex() {
    var rawPre =
      document.querySelector('#raw-listing-container pre') ||
      document.querySelector('pre')
    if (!rawPre) return

    var links = rawPre.querySelectorAll('a')
    if (!links || links.length === 0) return

    var items = []

    links.forEach(function (link) {
      var rawName = link.textContent.trim()
      var href = link.getAttribute('href')
      if (!rawName || !href) return

      // 忽略隱藏主題資料夾 .theme/
      if (rawName.startsWith('.theme') || href.includes('.theme')) {
        return
      }

      var date = '-'
      var size = '-'
      var isDir = false
      var isParent = false

      if (rawName === '../' || href === '../') {
        isParent = true
        isDir = true
      } else {
        isDir = rawName.endsWith('/') || href.endsWith('/')

        // 讀取 link 後方的純文字節點 (包含修改時間與大小)
        if (link.nextSibling && link.nextSibling.nodeType === 3) {
          var infoText = link.nextSibling.textContent.trim()
          if (infoText) {
            var parts = infoText.split(/\s+/)
            if (parts.length >= 2) {
              size = parts[parts.length - 1]
              date = parts.slice(0, parts.length - 1).join(' ')
            } else if (parts.length === 1) {
              size = parts[0]
            }
          }
        }
      }

      items.push({
        name: rawName,
        cleanName: isParent
          ? '.. (返回上一層目錄)'
          : isDir
          ? rawName.replace(/\/$/, '')
          : rawName,
        href: href,
        date: date,
        size: size === '-' && isDir ? '-' : size,
        isDir: isDir,
        isParent: isParent,
      })
    })

    // 排序：返回上一層第一、目錄次之、檔案最後，各自按字母排列
    items.sort(function (a, b) {
      if (a.isParent) return -1
      if (b.isParent) return 1
      if (a.isDir && !b.isDir) return -1
      if (!a.isDir && b.isDir) return 1
      return a.cleanName.localeCompare(b.cleanName, 'zh-Hant')
    })

    renderTable(items)
  }

  // 4. 渲染現代化表格
  function renderTable(items) {
    allItems = items
    var tbody = document.getElementById('files-tbody')
    if (!tbody) return
    tbody.innerHTML = ''

    var dirCount = 0
    var fileCount = 0

    items.forEach(function (item) {
      if (!item.isParent) {
        if (item.isDir) dirCount++
        else fileCount++
      }

      var meta = getFileMeta(item.name, item.isDir, item.isParent)
      var tr = document.createElement('tr')
      tr.setAttribute('data-name', item.cleanName.toLowerCase())

      // 絕對下載直鏈 (用於複製功能)
      var fullUrl = new URL(item.href, window.location.href).href

      // 名稱欄
      var tdName = document.createElement('td')
      tdName.innerHTML = `
        <a href="${item.href}" class="file-link">
          <i class="fas ${meta.icon} file-icon"></i>
          <span>${escapeHtml(item.cleanName)}</span>
          ${meta.badge ? `<span class="file-badge">${meta.badge}</span>` : ''}
        </a>
      `

      // 大小欄
      var tdSize = document.createElement('td')
      tdSize.className = 'text-right file-size'
      tdSize.textContent = item.size

      // 日期欄
      var tdDate = document.createElement('td')
      tdDate.className = 'text-center file-date'
      tdDate.textContent = item.date

      // 操作欄
      var tdAction = document.createElement('td')
      tdAction.className = 'text-center'
      if (item.isParent) {
        tdAction.innerHTML = `
          <a href="${item.href}" class="file-action-btn">
            <i class="fas fa-arrow-turn-up mr-1"></i>上一層
          </a>
        `
      } else if (item.isDir) {
        tdAction.innerHTML = `
          <a href="${item.href}" class="file-action-btn">
            <i class="fas fa-folder-open mr-1"></i>進入
          </a>
        `
      } else {
        tdAction.innerHTML = `
          <a href="${
            item.href
          }" download class="file-action-btn" title="直接下載檔案">
            <i class="fas fa-download mr-1"></i>下載
          </a>
          <button type="button" class="file-action-btn btn-copy" data-url="${escapeHtml(
            fullUrl,
          )}" title="複製 CDN 直鏈">
            <i class="fas fa-link mr-1"></i>直鏈
          </button>
        `
      }

      tr.appendChild(tdName)
      tr.appendChild(tdSize)
      tr.appendChild(tdDate)
      tr.appendChild(tdAction)
      tbody.appendChild(tr)
    })

    // 綁定複製按鈕事件
    tbody.querySelectorAll('.btn-copy').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault()
        var url = this.getAttribute('data-url')
        copyToClipboard(url)
      })
    })

    // 更新摘要
    updateSummary(items.length, dirCount, fileCount)

    // 標記解析完成，切換顯示現代化表格
    document.body.classList.add(
      'js-ready',
      'dl-body',
      'hold-transition',
      'layout-top-nav',
    )
  }

  // 5. 搜尋篩選功能
  var searchInput = document.getElementById('dl-search-input')
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      var query = this.value.trim().toLowerCase()
      var rows = document.querySelectorAll('#files-tbody tr')
      var visibleCount = 0

      rows.forEach(function (row) {
        var name = row.getAttribute('data-name') || ''
        if (!query || name.includes(query)) {
          row.style.display = ''
          visibleCount++
        } else {
          row.style.display = 'none'
        }
      })

      var emptyState = document.getElementById('empty-state')
      if (emptyState) {
        emptyState.style.display = visibleCount === 0 ? 'block' : 'none'
      }

      var summaryLeft = document.getElementById('summary-left')
      if (summaryLeft) {
        if (query) {
          summaryLeft.innerHTML =
            '<i class="fas fa-filter mr-1 text-info"></i>篩選出 <strong>' +
            visibleCount +
            '</strong> 項 (總計 ' +
            allItems.length +
            ' 項)'
        } else {
          var dirCount = allItems.filter(function (i) {
            return i.isDir && !i.isParent
          }).length
          var fileCount = allItems.filter(function (i) {
            return !i.isDir
          }).length
          updateSummary(allItems.length, dirCount, fileCount)
        }
      }
    })
  }

  // 更新摘要條文字
  function updateSummary(total, dirs, files) {
    var summaryLeft = document.getElementById('summary-left')
    if (summaryLeft) {
      summaryLeft.innerHTML =
        '<i class="fas fa-list-check mr-1 text-info"></i>當前目錄共 <strong>' +
        dirs +
        '</strong> 個資料夾，<strong>' +
        files +
        '</strong> 個檔案'
    }
  }

  // 複製文字至剪貼簿
  function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text).then(showToast).catch(fallbackCopy)
    } else {
      fallbackCopy()
    }

    function fallbackCopy() {
      var textarea = document.createElement('textarea')
      textarea.value = text
      textarea.style.position = 'fixed'
      textarea.style.opacity = '0'
      document.body.appendChild(textarea)
      textarea.select()
      try {
        document.execCommand('copy')
        showToast()
      } catch (err) {
        alert('複製失敗，直鏈網址為： ' + text)
      }
      document.body.removeChild(textarea)
    }
  }

  // 顯示 Toast 通知
  var toastTimer = null
  function showToast() {
    var toast = document.getElementById('dl-toast')
    if (!toast) return
    toast.classList.add('show')
    if (toastTimer) clearTimeout(toastTimer)
    toastTimer = setTimeout(function () {
      toast.classList.remove('show')
    }, 2200)
  }

  function escapeHtml(str) {
    return (str || '').replace(/[&<>"']/g, function (m) {
      return {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
      }[m]
    })
  }

  // 啟動解析 (相容同步與異步載入)
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      setupBreadcrumbs()
      parseNginxAutoindex()
    })
  } else {
    setupBreadcrumbs()
    parseNginxAutoindex()
  }
})()
