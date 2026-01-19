<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.html"); exit; }
$loggedInUser = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>YourMusic</title>
  <link rel="stylesheet" href="dashboard-style.css">
  <link rel="stylesheet" href="style.css"> 
  <style>
    /* === 1. ALINIERE USER SIDEBAR === */
    .user-info-container {
        padding: 15px 30px; /* Aliniat cu logo-ul */
        color: rgba(255,255,255,0.7);
        font-size: 0.9rem;
        text-align: left; /* Fortat la stanga */
        width: 100%;
        box-sizing: border-box;
    }
    .user-info-container b { color: white; }

    /* === 2. MENIURI & BUTOANE === */
    .list-item-actions { display: flex; align-items: center; gap: 15px; }
    .song-menu-container { position: relative; display: flex; align-items: center; }
    .song-menu-btn, .playlist-options-btn { 
        background: none; border: none; color: white; font-size: 22px; 
        cursor: pointer; padding: 0 5px; opacity: 0.7; line-height: 1; 
    }
    .song-menu-btn:hover, .playlist-options-btn:hover { opacity: 1; }

    .dropdown-menu {
        position: absolute; right: 0; top: 30px;
        background: #1a1a1a; border: 1px solid #444;
        border-radius: 6px; padding: 5px; display: none;
        flex-direction: column; gap: 2px; z-index: 200; width: 190px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.8);
    }
    .dropdown-menu.active { display: flex; }
    .dropdown-item {
        background: none; border: none; color: #ddd; text-align: left;
        padding: 10px; cursor: pointer; font-size: 0.85rem; border-radius: 4px; width: 100%;
    }
    .dropdown-item:hover { background: rgba(255,255,255,0.1); color: white; }
    .dropdown-item.danger { color: #ff5555; }
     
    .collab-input-group { display: flex; gap: 5px; padding: 8px; border-bottom: 1px solid #333; }
    .collab-input { width: 100%; padding: 6px; border-radius: 4px; border:none; font-size: 0.8rem; background: #fff; color: #000; }

    /* === 3. SLIDERS FIX (MOV + INALTIME CORECTA) === */
    input[type=range] { 
        -webkit-appearance: none; width: 100%; background: transparent; 
        cursor: pointer; height: 4px !important; margin: 10px 0; 
    }
    /* Thumb (Punctul) */
    input[type=range]::-webkit-slider-thumb { 
        -webkit-appearance: none; height: 12px; width: 12px; 
        border-radius: 50%; background: white; margin-top: -4px; 
        box-shadow: 0 0 5px rgba(0,0,0,0.5); 
    }
    /* Track (Bara) - Backgroundul e pus din JS */
    input[type=range]::-webkit-slider-runnable-track { width: 100%; height: 4px; border-radius: 2px; }

    /* Restul Design */
    .music-player { height: 90px; padding: 0 40px; display: flex; justify-content: space-between; align-items: center; background: rgba(20, 20, 20, 0.95); backdrop-filter: blur(12px); border-top: 1px solid rgba(255, 255, 255, 0.1); position: fixed; bottom: 0; left: 250px; right: 0; z-index: 100; }
    .song-info { width: 200px; display: flex; flex-direction: column; justify-content: center; }
    #player-song-name { font-weight: bold; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    #player-artist-name { font-size: 0.8rem; color: #ccc; }
    .player-center-group { flex: 1; max-width: 600px; display: flex; flex-direction: column; align-items: center; gap: 8px; }
    .player-controls { display: flex; align-items: center; gap: 20px; }
    .player-btn { background: none; border: none; cursor: pointer; color: white; opacity: 0.8; padding: 0; display: flex; }
    .play-btn svg { width: 42px; height: 42px; fill: white; }
    #prev-btn svg, #next-btn svg { width: 28px; height: 28px; fill: #eee; }
    .player-progress { width: 100%; display: flex; align-items: center; }
    .volume-control { width: 150px; display: flex; justify-content: flex-end; align-items: center; gap: 10px; }
    #volume-slider { width: 80px; }
    .upload-form input[type="text"] { width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px; border: none; background: rgba(255,255,255,0.9); color: #333; }
    .search-container { margin-bottom: 20px; }
    .search-input { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #444; background: rgba(255,255,255,0.1); color: white; outline: none; }
    .playlist-select-item { padding: 12px; border-bottom: 1px solid #333; cursor: pointer; }
    .playlist-select-item:hover { background: #333; }
    @media (max-width: 768px) { .music-player { left: 0; padding: 0 15px; height: 110px; flex-wrap: wrap; } .song-info { width: 100%; text-align: center; order: 1; margin-bottom: 5px; } .player-center-group { width: 100%; order: 2; margin-bottom: 5px; } .volume-control { display: none; } }
  </style>
</head>
<body>
  <div id="page-transition-overlay"></div>
  <div class="app-container">
    <nav class="sidebar">
      <div class="logo-container"><a href="#" class="nav-logo" data-page="home">yrm</a></div>
      <ul class="nav-menu">
        <li><a href="#" class="nav-link active" data-page="home">Home</a></li>
        <li><a href="#" class="nav-link" data-page="my-music">My Music</a></li>
        <li><a href="#" class="nav-link" data-page="playlists">Playlists</a></li>
      </ul>
      <div class="user-info-container">User: &nbsp;<b><?php echo $loggedInUser; ?></b></div>
      <div class="sidebar-footer"><a href="logout.php" class="nav-link" id="logout-link">Log Out</a></div>
    </nav>
    <main class="main-content" id="main-content"></main>
    <footer class="music-player">
      <div class="song-info"><span id="player-song-name">Select a song</span><span id="player-artist-name">...</span></div>
      <audio id="audio-element"></audio>
      <div class="player-center-group">
          <div class="player-controls">
            <button class="player-btn" id="prev-btn"><svg viewBox="0 0 24 24"><path d="M6 6h2v12H6zm3.5 6 8.5 6V6z"></path></svg></button>
            <button class="player-btn play-btn" id="play-btn" data-icon="play"><svg viewBox="0 0 24 24"><path d="M8 5.14v13.72L19.25 12 8 5.14z"></path></svg></button>
            <button class="player-btn" id="next-btn"><svg viewBox="0 0 24 24"><path d="m6 18 8.5-6L6 6v12zM16 6v12h2V6h-2z"></path></svg></button>
          </div>
          <div class="player-progress"><input type="range" id="progress-bar" value="0" min="0" max="100"></div>
      </div>
      <div class="volume-control">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"></path></svg>
        <input type="range" id="volume-slider" value="100" min="0" max="100">
      </div>
    </footer>
  </div>

  <div id="modal-new-playlist" class="modal-overlay">
    <div class="modal-content">
      <button class="modal-close" data-modal="modal-new-playlist">&times;</button>
      <h2>New Playlist</h2>
      <input type="text" id="new-playlist-name" placeholder="Playlist Name">
      <button class="modal-action-btn" id="create-playlist-btn">Create</button>
    </div>
  </div>
  <div id="modal-upload-music" class="modal-overlay">
    <div class="modal-content">
      <button class="modal-close" data-modal="modal-upload-music">&times;</button>
      <h2>Upload Song</h2>
      <div class="upload-form">
          <label>File (MP3/WAV)</label> <input type="file" id="upload-file-input" accept="audio/*">
          <label>Title</label> <input type="text" id="upload-title" placeholder="Song Title">
          <label>Artist</label> <input type="text" id="upload-artist" placeholder="Artist Name">
          <label>Album</label> <input type="text" id="upload-album" placeholder="Album Name">
          <button class="modal-action-btn" id="start-upload-btn" style="margin-top:20px;">Upload Now</button>
      </div>
    </div>
  </div>
  <div id="modal-select-playlist" class="modal-overlay">
    <div class="modal-content">
      <button class="modal-close" data-modal="modal-select-playlist">&times;</button>
      <h2>Select Playlist</h2>
      <div id="select-playlist-list" class="content-list" style="margin-top:15px; max-height:200px; overflow-y:auto;"></div>
    </div>
  </div>
  <div id="modal-add-to-playlist" class="modal-overlay">
    <div class="modal-content" style="max-height: 80vh; overflow-y: auto;">
      <button class="modal-close" data-modal="modal-add-to-playlist">&times;</button>
      <h2>Add Songs to Playlist</h2>
      <div class="content-list" id="add-song-list"></div>
    </div>
  </div>

  <script>
    const currentUser = "<?php echo $loggedInUser; ?>";
    let currentPlaylistSongs = [];
    let currentSongIndex = -1;
    let allMySongs = []; 
    let songIdToAdd = null; 
    let activePlaylistId = null;

    const audioElement = document.getElementById('audio-element');
    const playBtn = document.getElementById('play-btn');
    const progressBar = document.getElementById('progress-bar');
    const volumeSlider = document.getElementById('volume-slider');

    // === BAR COLOR FIX (HEIGHT = TRACK HEIGHT) ===
    function updateRangeBackground(input) {
        const val = input.value;
        const min = input.min || 0;
        const max = input.max || 100;
        const perc = ((val - min) / (max - min)) * 100;
        input.style.background = `linear-gradient(to right, #8a2be2 ${perc}%, rgba(255, 255, 255, 0.2) ${perc}%)`;
    }
    updateRangeBackground(progressBar);
    updateRangeBackground(volumeSlider);

    const pageContent = {
      'home': `<div class="page-header"><h1>Hi</h1></div><h2>Recently Added</h2><div class="content-list" id="home-recent-list">Loading...</div>`,
      'my-music': `<div class="page-header"><h1>My Music</h1><button class="glass-btn" id="btn-open-upload">Upload Song</button></div><div class="search-container"><input type="text" id="music-search" class="search-input" placeholder="Search..."></div><div class="content-list" id="music-list-container">Loading...</div>`,
      'playlists': `<div class="page-header"><h1>Playlists</h1><button class="glass-btn" id="btn-new-playlist">New Playlist</button></div><div class="content-grid" id="playlists-container">Loading...</div>`,
      'playlist-detail': `<div id="playlist-detail-view">Loading...</div>`
    };

    function loadContent(pageKey, playlistId = null) {
        document.getElementById('page-transition-overlay').classList.add('fade-out');
        const main = document.getElementById('main-content');
        main.style.opacity = 0;
        setTimeout(() => {
            if (pageKey === 'playlist-detail' && playlistId) { loadPlaylistDetails(playlistId); } 
            else {
                main.innerHTML = pageContent[pageKey];
                if(pageKey === 'my-music') loadMyMusic();
                if(pageKey === 'home') loadHome();
                if(pageKey === 'playlists') loadPlaylists();
                if(pageKey === 'my-music') attachSearchListener();
            }
            main.style.opacity = 1;
            document.getElementById('page-transition-overlay').classList.remove('fade-out');
        }, 300);
    }

    // === COLLABORATOR LOGIC (BUG FIX) ===
    window.manageCollab = function(e, type) {
        e.stopPropagation(); // Butonul nu închide meniul
        const inputId = type === 'add' ? 'collab-name-add' : 'collab-name-rem';
        const input = document.getElementById(inputId);
        const u = input.value;
        if(!u) return;

        const action = type === 'add' ? 'add_collab' : 'remove_collab';
         
        fetch(`playlist_api.php?action=${action}`, { 
            method:'POST', 
            body:JSON.stringify({playlist_id:activePlaylistId, username:u}) 
        })
        .then(res => {
            if(!res.ok) throw new Error("Server error " + res.status);
            return res.json();
        })
        .then(d => { 
            if(d.success) { 
                alert(type === 'add' ? "Utilizator adăugat!" : "Utilizator șters!"); 
                input.value = ''; 
                loadPlaylistDetails(activePlaylistId); 
            }
            else { alert("Eroare: " + d.message); }
        })
        .catch(err => alert("Eroare de conexiune: " + err.message));
    };

    // === PLAYLIST DETAILS ===
    function loadPlaylistDetails(id) {
        activePlaylistId = id;
        fetch(`playlist_api.php?action=get_details&id=${id}`)
        .then(res=>res.json())
        .then(data => {
            if(!data.success) { alert(data.message); return loadContent('playlists'); }
            currentPlaylistSongs = data.songs;
             
            let html = `
                <div class="page-header playlist-header-row" style="display:flex; justify-content:space-between;">
                    <div><h1>${data.playlist.name}</h1><div class="card-subtitle">Collabs: ${data.collaborators.join(', ') || 'None'}</div></div>
                    <div style="position:relative;">
                        <button class="playlist-options-btn" onclick="toggleMenu(event, 'pl-settings')">⋮</button>
                         
                        <div id="pl-settings" class="dropdown-menu">
                            <div class="collab-input-group" onclick="event.stopPropagation()">
                                <input id="collab-name-add" class="collab-input" placeholder="Add User">
                                <button onclick="manageCollab(event, 'add')" class="glass-btn small" style="padding:2px 8px;">+</button>
                            </div>
                            <div class="collab-input-group" onclick="event.stopPropagation()">
                                <input id="collab-name-rem" class="collab-input" placeholder="Remove User">
                                <button onclick="manageCollab(event, 'rem')" class="glass-btn small" style="padding:2px 8px;">-</button>
                            </div>
                            <button class="dropdown-item danger" onclick="delPlaylist()">Delete Playlist</button>
                        </div>
                    </div>
                </div>
                <button class="glass-btn" id="btn-add-song-pl" style="margin-bottom:20px;">+ Add Songs</button>
                <div class="content-list">`;
             
            if(data.songs.length === 0) html += `<div class="list-item">Empty playlist.</div>`;
            data.songs.forEach((s, idx) => {
                html += `
                <div class="list-item">
                    <div class="list-item-main">${s.title} - ${s.artist}</div>
                    <div class="list-item-actions">
                        <a href="${s.file_path}" download="${s.title}.mp3" class="download-btn" title="Download Song">⬇</a>

                        <button class="glass-btn" onclick="playSong(${idx})">▶</button>
                        <button class="btn-remove-song" onclick="remSong(${s.song_id})">✕</button>
                    </div>
                </div>`;
            });
            html += `</div>`;
            document.getElementById('main-content').innerHTML = html;
            document.getElementById('btn-add-song-pl').addEventListener('click', openAddSongModal);
        });
    }

    // === STANDARD FUNCTIONS (Home, My Music) ===
    function loadHome() {
        fetch('get_music.php').then(res=>res.json()).then(songs => {
            const div = document.getElementById('home-recent-list');
            if(!songs || songs.length===0) { div.innerHTML='No recent songs.'; return; }
            const rec = songs.slice(0,5); div.innerHTML='';
            rec.forEach(s => div.innerHTML += `<div class="list-item"><div class="list-item-main">${s.title} - ${s.artist}</div></div>`);
        });
    }
     
    function loadMyMusic() {
        fetch('get_music.php').then(res=>res.json()).then(songs => {
            allMySongs = songs; currentPlaylistSongs = songs;
            renderList(songs);
        });
    }

    function renderList(songs) {
        const div = document.getElementById('music-list-container');
        div.innerHTML = '';
        if(songs.length === 0) { div.innerHTML = 'Empty.'; return; }
        songs.forEach((s, idx) => {
            div.innerHTML += `
            <div class="list-item">
                <div class="list-item-main">
                    <div class="list-item-title">${s.title}</div>
                    <div class="list-item-sub">${s.artist}</div>
                </div>
                <div class="list-item-actions">
                    <a href="${s.file_path}" download="${s.title}.mp3" class="download-btn" title="Download Song">⬇</a>
                     
                    <button class="glass-btn play-my-song" data-index="${idx}">▶</button>
                     
                    <div class="song-menu-container">
                        <button class="song-menu-btn" onclick="toggleMenu(event, 'menu-${s.song_id}')">⋮</button>
                        <div id="menu-${s.song_id}" class="dropdown-menu">
                            <button class="dropdown-item" onclick="openAddToPlaylistModal(${s.song_id})">Add to Playlist</button>
                            <button class="dropdown-item danger" onclick="deleteSong(${s.song_id})">Delete Song</button>
                        </div>
                    </div>
                </div>
            </div>`;
        });
        document.querySelectorAll('.play-my-song').forEach(btn => btn.onclick = (e) => playSong(parseInt(e.target.dataset.index)));
    }

    // === FIX MENIU (Z-INDEX) ===
    window.toggleMenu = function(e, id) {
        e.stopPropagation();
        
        // 1. Închide toate celelalte meniuri active
        document.querySelectorAll('.dropdown-menu').forEach(m => {
            if (m.id !== id) m.classList.remove('active');
        });

        // 2. Resetează Z-Index-ul la TOATE rândurile (le trimite în spate)
        document.querySelectorAll('.list-item').forEach(item => {
            item.style.zIndex = "1";
        });

        // 3. Deschide/Închide meniul curent
        const menu = document.getElementById(id);
        menu.classList.toggle('active');

        // 4. Dacă meniul s-a deschis, adu rândul părinte ÎN FAȚĂ (Z-Index mare)
        if (menu.classList.contains('active')) {
            const parentRow = menu.closest('.list-item');
            if (parentRow) {
                parentRow.style.zIndex = "1000";
            }
        }
    };
    document.addEventListener('click', () => document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('active')));

    window.openAddToPlaylistModal = function(songId) {
        songIdToAdd = songId;
        document.getElementById('modal-select-playlist').style.display = 'flex';
        const list = document.getElementById('select-playlist-list');
        list.innerHTML = 'Loading...';
        fetch('get_playlists.php').then(res=>res.json()).then(pls => {
            list.innerHTML = '';
            if(pls.length === 0) { list.innerHTML = 'No playlists found.'; return; }
            pls.forEach(pl => {
                const item = document.createElement('div');
                item.className = 'playlist-select-item';
                item.textContent = pl.name;
                item.onclick = () => {
                    fetch(`playlist_api.php?action=add_song`, { method: 'POST', body: JSON.stringify({ playlist_id: pl.playlist_id, song_id: songIdToAdd }) })
                    .then(res=>res.json()).then(d => { if(d.success) alert("Added!"); else alert(d.message); document.getElementById('modal-select-playlist').style.display='none'; });
                };
                list.appendChild(item);
            });
        });
    };

    // === DELETE SONG FUNCTION ===
    window.deleteSong = function(sid) {
        if(!confirm("Ești sigur că vrei să ștergi definitiv melodia?")) return;

        // Construim datele pentru formular (simulăm un form submit)
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('song_id', sid);

        fetch('upload.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert("Melodie ștearsă!");
                loadMyMusic(); // Reîncărcăm lista ca să dispară melodia
            } else {
                alert("Eroare: " + data.message);
            }
        })
        .catch(err => alert("Eroare de conexiune."));
    };

    function loadPlaylists() {
        fetch('get_playlists.php').then(res=>res.json()).then(pls => {
            const div = document.getElementById('playlists-container');
            div.innerHTML = '';
            if(pls.length===0) { div.innerHTML='No playlists.'; return; }
            pls.forEach(pl => {
                const c = document.createElement('div'); c.className='glass-card';
                c.innerHTML=`<div class="card-title">${pl.name}</div>`;
                c.onclick = () => loadContent('playlist-detail', pl.playlist_id);
                div.appendChild(c);
            });
        });
    }

    // Player, Upload, Create Playlist (Same logic)
    function playSong(idx) { if(idx<0||idx>=currentPlaylistSongs.length)return; currentSongIndex=idx; const s=currentPlaylistSongs[idx]; audioElement.src=s.file_path; document.getElementById('player-song-name').textContent=s.title; document.getElementById('player-artist-name').textContent=s.artist; audioElement.play(); playBtn.innerHTML=`<svg viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"></path></svg>`; }
    playBtn.onclick=()=>{ if(audioElement.paused){audioElement.play();playBtn.innerHTML=`<svg viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"></path></svg>`;}else{audioElement.pause();playBtn.innerHTML=`<svg viewBox="0 0 24 24"><path d="M8 5.14v13.72L19.25 12 8 5.14z"></path></svg>`;} };
    document.getElementById('prev-btn').onclick=()=>playSong(currentSongIndex-1);
    document.getElementById('next-btn').onclick=()=>playSong(currentSongIndex+1);
    audioElement.onended=()=>playSong(currentSongIndex+1);
    audioElement.ontimeupdate=()=>{ if(audioElement.duration){ progressBar.value=(audioElement.currentTime/audioElement.duration)*100; updateRangeBackground(progressBar); } };
    progressBar.oninput=(e)=>{ audioElement.currentTime=(e.target.value/100)*audioElement.duration; updateRangeBackground(progressBar); };
    volumeSlider.oninput=(e)=>{ audioElement.volume=e.target.value/100; updateRangeBackground(volumeSlider); };

    document.body.addEventListener('click', (e) => {
        if(e.target.id === 'btn-open-upload') document.getElementById('modal-upload-music').style.display = 'flex';
        if(e.target.id === 'btn-new-playlist') document.getElementById('modal-new-playlist').style.display = 'flex';
        if(e.target.classList.contains('modal-close')) e.target.closest('.modal-overlay').style.display = 'none';
    });

    document.getElementById('start-upload-btn').onclick = () => {
        const f = document.getElementById('upload-file-input').files[0];
        const t = document.getElementById('upload-title').value;
        const a = document.getElementById('upload-artist').value;
        const al = document.getElementById('upload-album').value;
        if(!f) return alert("File required");
        const fd = new FormData(); fd.append('music_file', f); fd.append('title', t); fd.append('artist', a); fd.append('album', al);
        fetch('upload.php', {method:'POST', body:fd}).then(r=>r.json()).then(d=>{ if(d.success){alert("Done"); loadMyMusic(); document.getElementById('modal-upload-music').style.display='none';} else alert(d.message); });
    };
    document.getElementById('create-playlist-btn').onclick = () => {
        const n = document.getElementById('new-playlist-name').value;
        if(!n) return;
        fetch('create_playlist.php', {method:'POST', body:JSON.stringify({name:n})}).then(r=>r.json()).then(d=>{ if(d.success){loadPlaylists(); document.getElementById('modal-new-playlist').style.display='none';} });
    };
    function attachSearchListener() {
        const inp = document.getElementById('music-search');
        if(inp) inp.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            const filt = allMySongs.filter(s => s.title.toLowerCase().includes(term) || s.artist.toLowerCase().includes(term));
            renderList(filt);
        });
    }
    // Helpers Playlist
    window.delPlaylist = function() { if(confirm("Delete?")) fetch(`playlist_api.php?action=delete_playlist`, { method:'POST', body:JSON.stringify({playlist_id:activePlaylistId}) }).then(r=>r.json()).then(d=>loadContent('playlists')); };
    window.remSong = function(sid) { fetch(`playlist_api.php?action=remove_song`, { method:'POST', body:JSON.stringify({playlist_id:activePlaylistId, song_id:sid}) }).then(r=>r.json()).then(d=>loadPlaylistDetails(activePlaylistId)); };
    function openAddSongModal() {
        document.getElementById('modal-add-to-playlist').style.display = 'flex';
        const list = document.getElementById('add-song-list'); list.innerHTML = 'Loading...';
        fetch('get_music.php').then(res=>res.json()).then(songs => {
            list.innerHTML = '';
            songs.forEach(song => {
                const btn = document.createElement('div'); btn.className = 'list-item'; btn.style.cursor = 'pointer';
                btn.innerHTML = `<div class="list-item-main">${song.title}</div><div class="list-item-actions">Add</div>`;
                btn.onclick = () => {
                    fetch(`playlist_api.php?action=add_song`, { method: 'POST', body: JSON.stringify({ playlist_id: activePlaylistId, song_id: song.song_id }) })
                    .then(res=>res.json()).then(d=>{ if(d.success){ alert("Added!"); loadPlaylistDetails(activePlaylistId); }});
                };
                list.appendChild(btn);
            });
        });
    }

    window.onload = () => { document.getElementById('page-transition-overlay').style.opacity = '0'; loadContent('home'); };
    document.querySelectorAll('.nav-link').forEach(l => l.onclick = (e) => {
        if(l.id === 'logout-link') return; e.preventDefault();
        document.querySelectorAll('.nav-link').forEach(x=>x.classList.remove('active')); l.classList.add('active');
        loadContent(l.dataset.page);
    });
  </script>
</body>
</html>