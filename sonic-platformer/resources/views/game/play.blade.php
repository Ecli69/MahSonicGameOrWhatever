@extends('layouts.app')
@section('content')
    <h1>Green Hill Zone</h1>
    <p>Стрелки / A, D — бег. Пробел — прыжок. Вниз + Пробел (стоя) — Спин-дэш. Собери кольца и добеги до флага!</p>
    
    <div style="text-align: center;">
        <canvas id="gameCanvas" width="900" height="450" style="border: 3px solid #ffcc00; background: #5c94fc; border-radius: 10px;"></canvas>
        <p>Нажми <b>Enter</b>, чтобы начать</p>
    </div>

<script>
    const canvas = document.getElementById('gameCanvas');
    const ctx = canvas.getContext('2d');

    // --- ЗАГРУЗКА СПРАЙТОВ ---
    const sprites = {
        idle: new Image(), walk: [], run: [], jump: [], motobug: [], title: new Image(), 
        down: new Image(), gameover: new Image(), win: new Image()
    };
    sprites.idle.src = "/images/sonic_idle.png";
    sprites.title.src = "/images/title.png";
    sprites.down.src = "/images/sonic_down.png";
    sprites.gameover.src = "/images/GameOver.png";
    sprites.win.src = "/images/WinScreen.png";

    for (let i = 1; i <= 8; i++) { let img = new Image(); img.src = `/images/sonic_walk_${i}.png`; sprites.walk.push(img); }
    for (let i = 1; i <= 4; i++) { let img = new Image(); img.src = `/images/sonic_run_${i}.png`; sprites.run.push(img); }
    for (let i = 1; i <= 5; i++) { let img = new Image(); img.src = `/images/sonic_jump_${i}.png`; sprites.jump.push(img); }
    for (let i = 1; i <= 2; i++) { let img = new Image(); img.src = `/images/motobug_${i}.png`; sprites.motobug.push(img); }

    const camera = { x: 0, y: 0 };

    // --- ИГРОК ---
    const player = {
        x: 100, y: 440, 
        width: 40, height: 50,
        dx: 0, dy: 0,
        baseMaxSpeed: 12, 
        speedLimit: 12,   
        acceleration: 0.1, 
        friction: 0.15,   
        jumpPower: -14,
        grounded: false, ringsCollected: 0, facingRight: true,
        state: 'idle', isRolling: false, isDown: false, frame: 0, tick: 0,
        invulnerable: 0, opacity: 1.0,
        charge: 0, isCharging: false
    };

    const gravity = 0.7;

    // --- ЛАНДШАФТ ---
    const terrainLeft = [
        { x: 0, y: 500 }, { x: 1000, y: 500 },
        { x: 1400, y: 400 },
        { x: 1800, y: 300 }, { x: 2400, y: 300 },
        { x: 2600, y: 350 }, { x: 2800, y: 400 }, 
        { x: 3000, y: 480 }, { x: 3200, y: 550 }
    ];

    const terrainRight = [
        { x: 4400, y: 500 }, { x: 5000, y: 500 },
        { x: 5300, y: 400 }, { x: 6000, y: 400 }
    ];

    const platforms = [
        { x: 3350, y: 450, width: 80, height: 20 },
        { x: 3550, y: 400, width: 80, height: 20 },
        { x: 3800, y: 450, width: 80, height: 20 },
        { x: 4050, y: 400, width: 80, height: 20 },
        { x: 4250, y: 450, width: 80, height: 20 }
    ];

    let rings = [
        { x: 1100, y: 400, collected: false, vx: 0, vy: 0 }, { x: 1500, y: 350, collected: false, vx: 0, vy: 0 },
        { x: 2000, y: 250, collected: false, vx: 0, vy: 0 }, { x: 2200, y: 250, collected: false, vx: 0, vy: 0 },
        { x: 2800, y: 350, collected: false, vx: 0, vy: 0 }, { x: 3100, y: 500, collected: false, vx: 0, vy: 0 },
        { x: 3450, y: 400, collected: false, vx: 0, vy: 0 }, { x: 3900, y: 400, collected: false, vx: 0, vy: 0 },
        { x: 4600, y: 400, collected: false, vx: 0, vy: 0 }, { x: 5100, y: 400, collected: false, vx: 0, vy: 0 }
    ];

    let enemies = [
        { x: 2000, y: 270, width: 40, height: 30, dx: 2, minX: 1800, maxX: 2400, alive: true, frame: 0, tick: 0 }
    ];

    const finishLine = { x: 5700, y: 300, width: 50, height: 100 };

    let keys = {};
    let gameState = 'title'; // 'title', 'playing', 'gameover', 'win'
    let startTime, timeElapsed = 0;

    // --- УПРАВЛЕНИЕ ---
    window.addEventListener('keydown', (e) => {
        if (['Space', 'ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Enter'].includes(e.code)) e.preventDefault();
        
        if (!keys[e.code]) {
            if ((e.code === 'Space' || e.code === 'ArrowUp') && gameState === 'playing') {
                if (keys['ArrowDown'] && player.grounded && Math.abs(player.dx) < 0.5 && !player.isRolling) {
                    player.charge = Math.min(player.charge + 5, 20);
                } 
                else if (player.grounded) {
                    player.dy = player.jumpPower;
                    player.grounded = false;
                    player.isRolling = false; 
                    player.isCharging = false;
                }
            }
            if (e.code === 'Enter' && (gameState === 'title' || gameState === 'gameover' || gameState === 'win')) {
                startGame();
            }
        }
        keys[e.code] = true;
    });
    
    window.addEventListener('keyup', (e) => {
        keys[e.code] = false;
        if (e.code === 'ArrowDown' && player.charge > 0) {
            player.dx = player.facingRight ? player.charge : -player.charge;
            player.isRolling = true;
            player.speedLimit = 20;
            player.charge = 0;
        }
    });

    function startGame() {
        player.x = 100; player.y = 440; player.dx = 0; player.dy = 0;
        player.ringsCollected = 0; player.grounded = false; player.isRolling = false;
        player.state = 'idle'; player.frame = 0; player.invulnerable = 0; player.opacity = 1.0;
        player.charge = 0; player.isCharging = false; player.speedLimit = player.baseMaxSpeed;
        player.isDown = false;
        rings.forEach(r => { r.collected = false; r.vx = 0; r.vy = 0; });
        enemies[0].x = 2000; enemies[0].alive = true;
        
        camera.x = player.x - canvas.width / 2 + player.width / 2;
        camera.y = player.y - canvas.height / 2 + player.height / 2;
        
        startTime = Date.now(); timeElapsed = 0; gameState = 'playing';
    }

    function getGroundY(terrainArray) {
        for (let i = 0; i < terrainArray.length - 1; i++) {
            let p1 = terrainArray[i]; let p2 = terrainArray[i + 1];
            if (player.x + player.width / 2 >= p1.x && player.x + player.width / 2 <= p2.x) {
                let t = (player.x + player.width / 2 - p1.x) / (p2.x - p1.x);
                return p1.y + (p2.y - p1.y) * t;
            }
        }
        return null;
    }

    function update() {
        if (gameState !== 'playing') return;
        
        timeElapsed = ((Date.now() - startTime) / 1000).toFixed(2);
        if (player.invulnerable > 0) player.invulnerable -= 1/60;

        const movingRight = keys['ArrowRight'] || keys['KeyD'];
        const movingLeft = keys['ArrowLeft'] || keys['KeyA'];
        const pressingDown = keys['ArrowDown'];
        
        if (player.charge > 0 && pressingDown && player.grounded) {
            player.isCharging = true;
            player.isRolling = false; 
        } else {
            player.isCharging = false;
        }

        if (!player.isCharging) {
            // Логика приседа
            if (pressingDown && player.grounded && Math.abs(player.dx) < 0.5) {
                player.isDown = true;
                player.isRolling = false;
            } else {
                player.isDown = false;
            }

            // Логика клубка
            if (pressingDown && player.grounded && Math.abs(player.dx) > 0.5) {
                player.isRolling = true;
            } else if (!player.grounded) {
                player.isRolling = false;
            } else if (!movingRight && !movingLeft && Math.abs(player.dx) < 1) {
                player.isRolling = false;
            }

            let currentFriction = player.friction;
            if ((player.dx > 0 && movingLeft) || (player.dx < 0 && movingRight)) currentFriction *= 2;

            if (movingRight && !player.isRolling) { player.dx += player.acceleration; player.facingRight = true; }
            else if (movingLeft && !player.isRolling) { player.dx -= player.acceleration; player.facingRight = false; }
            else if (Math.abs(player.dx) > 0) {
                if (player.dx > 0) player.dx -= currentFriction;
                if (player.dx < 0) player.dx += currentFriction;
            }

            if (Math.abs(player.dx) <= player.baseMaxSpeed) player.speedLimit = player.baseMaxSpeed;
            if (player.dx > player.speedLimit) player.dx = player.speedLimit;
            if (player.dx < -player.speedLimit) player.dx = -player.speedLimit;
            
            if (!movingRight && !movingLeft && Math.abs(player.dx) < 0.1) player.dx = 0;
        }

        let prevY = player.y;
        let wasGrounded = player.grounded;

        player.x += player.dx;
        
        if (wasGrounded && player.dy >= 0) {
            let groundY = getGroundY(terrainLeft) || getGroundY(terrainRight);
            if (groundY !== null) {
                player.y = groundY - player.height;
                player.dy = 0;
                player.grounded = true;
            } else {
                player.dy += gravity;
                player.y += player.dy;
                player.grounded = false;
            }
        } else {
            player.dy += gravity;
            player.y += player.dy;
            player.grounded = false;
        }

        function checkCollision(terrainArray) {
            for (let i = 0; i < terrainArray.length - 1; i++) {
                let p1 = terrainArray[i]; let p2 = terrainArray[i + 1];
                if (player.x + player.width / 2 >= p1.x && player.x + player.width / 2 <= p2.x) {
                    let t = (player.x + player.width / 2 - p1.x) / (p2.x - p1.x);
                    let groundY = p1.y + (p2.y - p1.y) * t;

                    if (player.dy >= 0 && (player.y + player.height) >= groundY && (prevY + player.height) <= groundY + 20) {
                        player.y = groundY - player.height;
                        player.dy = 0;
                        player.grounded = true;

                        let slopeForce = 0;
                        if (p2.y > p1.y) slopeForce = 0.15;
                        if (p2.y < p1.y) slopeForce = -0.15;
                        if (player.isRolling) slopeForce *= 1.5;
                        player.dx += slopeForce;
                    }
                }
            }
        }
        checkCollision(terrainLeft);
        checkCollision(terrainRight);

        platforms.forEach(plat => {
            if (player.x < plat.x + plat.width && player.x + player.width > plat.x &&
                player.y < plat.y + plat.height && player.y + player.height > plat.y) {
                if (player.dy >= 0 && (prevY + player.height) <= plat.y + 10) {
                    player.y = plat.y - player.height; player.dy = 0; player.grounded = true;
                }
            }
        });

        // Враги
        enemies.forEach(enemy => {
            if (!enemy.alive) return;
            enemy.x += enemy.dx;
            if (enemy.x > enemy.maxX || enemy.x < enemy.minX) enemy.dx *= -1;
            enemy.tick++; if (enemy.tick > 10) { enemy.frame = (enemy.frame + 1) % 2; enemy.tick = 0; }

            if (player.x < enemy.x + enemy.width && player.x + player.width > enemy.x &&
                player.y < enemy.y + enemy.height && player.y + player.height > enemy.y) {
                if (!player.grounded || player.isRolling) {
                    enemy.alive = false;
                    if (!player.isRolling) player.dy = -8;
                } else if (player.invulnerable <= 0) {
                    if (player.ringsCollected > 0) {
                        player.ringsCollected = 0;
                        rings.forEach(ring => {
                            ring.collected = false; ring.x = player.x; ring.y = player.y;
                            ring.vx = (Math.random() - 0.5) * 10; ring.vy = -Math.random() * 10 - 5;
                        });
                        player.dx = player.facingRight ? -5 : 5; player.dy = -6; player.invulnerable = 2;
                    } else {
                        gameState = 'gameover';
                    }
                }
            }
        });

        // Кольца
        rings.forEach(ring => {
            if (ring.collected && ring.vx !== 0) {
                ring.x += ring.vx; ring.y += ring.vy; ring.vy += 0.5;
                if (ring.y > 600) { ring.vx = 0; ring.vy = 0; }
            }
        });

        rings.forEach(ring => {
            if (!ring.collected && ring.vx === 0 && 
                player.x < ring.x + 15 && player.x + player.width > ring.x &&
                player.y < ring.y + 15 && player.y + player.height > ring.y) {
                ring.collected = true; player.ringsCollected++;
            }
        });

        if (player.y > 800) { gameState = 'gameover'; }
        if (player.x + player.width > finishLine.x) { finishGame(); }

        updateAnimationState();
        updateCamera();
    }

    function updateCamera() {
        let targetX = player.x - canvas.width / 2 + player.width / 2;
        let targetY = player.y - canvas.height / 2 + player.height / 2;

        camera.x += (targetX - camera.x) * 0.1;
        camera.y += (targetY - camera.y) * 0.1;

        if (camera.x < 0) camera.x = 0;
        if (camera.y < 0) camera.y = 0;
        if (camera.y > 150) camera.y = 150; 
    }

    function updateAnimationState() {
        player.tick++;
        if (player.invulnerable > 0 && Math.floor(player.invulnerable * 10) % 2 === 0) player.opacity = 0.3;
        else player.opacity = 1.0;

        if (player.isCharging) {
            player.state = 'jump'; 
            if (player.tick > 2) { player.frame = (player.frame + 1) % 5; player.tick = 0; }
        } else if (player.isDown) {
            player.state = 'down';
            player.frame = 0;
        } else if (!player.grounded || player.isRolling) {
            player.state = 'jump';
            if (player.tick > 5) { player.frame = (player.frame + 1) % 5; player.tick = 0; }
        } else if (Math.abs(player.dx) > 0.5) {
            if (Math.abs(player.dx) > 6) { player.state = 'run'; if (player.tick > 3) { player.frame = (player.frame + 1) % 4; player.tick = 0; } }
            else { player.state = 'walk'; if (player.tick > 5) { player.frame = (player.frame + 1) % 8; player.tick = 0; } }
        } else {
            player.state = 'idle'; player.frame = 0;
        }
    }

    function drawTerrain(terrainArray) {
        ctx.fillStyle = '#0b5e1a';
        ctx.beginPath();
        ctx.moveTo(terrainArray[0].x, terrainArray[0].y);
        for (let i = 1; i < terrainArray.length; i++) {
            ctx.lineTo(terrainArray[i].x, terrainArray[i].y);
        }
        ctx.lineTo(terrainArray[terrainArray.length - 1].x, 1000);
        ctx.lineTo(terrainArray[0].x, 1000);
        ctx.closePath();
        ctx.fill();

        ctx.beginPath();
        ctx.moveTo(terrainArray[0].x, terrainArray[0].y);
        for (let i = 1; i < terrainArray.length; i++) {
            ctx.lineTo(terrainArray[i].x, terrainArray[i].y);
        }
        ctx.strokeStyle = '#4caf50'; ctx.lineWidth = 10; ctx.stroke();
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        if (gameState === 'title') {
            if (sprites.title.complete && sprites.title.naturalWidth > 0) ctx.drawImage(sprites.title, 0, 0, canvas.width, canvas.height);
            else { ctx.fillStyle = '#0b1c3c'; ctx.fillRect(0, 0, canvas.width, canvas.height); }
            ctx.fillStyle = '#FFFFFF'; ctx.font = '30px Arial'; ctx.textAlign = 'center';
            ctx.globalAlpha = 0.5 + Math.sin(Date.now() / 300) * 0.5;
            ctx.fillText("Нажми Enter, чтобы начать", canvas.width / 2, canvas.height - 50);
            ctx.globalAlpha = 1.0;
            return;
        }

        if (gameState === 'gameover') {
            if (sprites.gameover.complete && sprites.gameover.naturalWidth > 0) {
                ctx.drawImage(sprites.gameover, 0, 0, canvas.width, canvas.height);
            } else {
                ctx.fillStyle = 'rgba(0,0,0,0.8)'; ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.fillStyle = '#FF0000'; ctx.font = '50px Arial'; ctx.textAlign = 'center';
                ctx.fillText("Game Over", canvas.width / 2, canvas.height / 2 - 50);
            }
            ctx.fillStyle = '#FFFFFF'; ctx.font = '20px Arial'; ctx.textAlign = 'center';
            ctx.fillText("Нажми Enter, чтобы попробовать снова", canvas.width / 2, canvas.height - 30);
            return;
        }

        if (gameState === 'win') {
            if (sprites.win.complete && sprites.win.naturalWidth > 0) {
                ctx.drawImage(sprites.win, 0, 0, canvas.width, canvas.height);
            } else {
                ctx.fillStyle = '#0b1c3c'; ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.fillStyle = '#ffcc00'; ctx.font = '50px Arial'; ctx.textAlign = 'center';
                ctx.fillText("Победа!", canvas.width / 2, canvas.height / 2 - 50);
            }
            ctx.fillStyle = '#FFFFFF'; ctx.font = '24px Arial'; ctx.textAlign = 'center';
            ctx.fillText(`Твой результат: ${timeElapsed} секунд`, canvas.width / 2, canvas.height / 2 + 20);
            ctx.fillText(`Собрано колец: ${player.ringsCollected}`, canvas.width / 2, canvas.height / 2 + 60);
            
            ctx.font = '20px Arial';
            ctx.fillText("Нажми Enter, чтобы сыграть еще раз", canvas.width / 2, canvas.height - 30);
            return;
        }

        ctx.save();
        ctx.translate(-Math.floor(camera.x), -Math.floor(camera.y));

        drawTerrain(terrainLeft);
        drawTerrain(terrainRight);

        platforms.forEach(plat => {
            ctx.fillStyle = '#228B22'; ctx.fillRect(plat.x, plat.y, plat.width, plat.height);
            ctx.fillStyle = '#32CD32'; ctx.fillRect(plat.x, plat.y, plat.width, 5);
        });

        ctx.fillStyle = '#FF1493'; ctx.fillRect(finishLine.x, finishLine.y, finishLine.width, finishLine.height);

        rings.forEach(ring => {
            if (!ring.collected || ring.vx !== 0) {
                ctx.beginPath(); ctx.arc(ring.x + 7, ring.y + 7, 7, 0, Math.PI * 2);
                ctx.strokeStyle = '#ffcc00'; ctx.lineWidth = 3; ctx.stroke();
            }
        });

        enemies.forEach(enemy => {
            if (enemy.alive) {
                if (sprites.motobug[enemy.frame] && sprites.motobug[enemy.frame].complete && sprites.motobug[enemy.frame].naturalWidth > 0) {
                    ctx.drawImage(sprites.motobug[enemy.frame], enemy.x, enemy.y, enemy.width, enemy.height);
                } else {
                    ctx.fillStyle = 'red'; ctx.fillRect(enemy.x, enemy.y, enemy.width, enemy.height);
                }
            }
        });

        ctx.globalAlpha = player.opacity;
        let currentSprite = null;
        if (player.state === 'idle' && sprites.idle.complete) currentSprite = sprites.idle;
        else if (player.state === 'down' && sprites.down.complete) currentSprite = sprites.down;
        else if (player.state === 'walk' && sprites.walk[player.frame] && sprites.walk[player.frame].complete) currentSprite = sprites.walk[player.frame];
        else if (player.state === 'run' && sprites.run[player.frame] && sprites.run[player.frame].complete) currentSprite = sprites.run[player.frame];
        else if (player.state === 'jump' && sprites.jump[player.frame] && sprites.jump[player.frame].complete) currentSprite = sprites.jump[player.frame];

        if (currentSprite) {
            ctx.save();
            let offsetX = 0;
            if (player.isCharging) offsetX = (Math.random() - 0.5) * 4;

            if (!player.facingRight) { ctx.scale(-1, 1); ctx.translate(-player.x - player.width, 0); }
            ctx.drawImage(currentSprite, player.facingRight ? player.x + offsetX : offsetX, player.y, player.width, player.height);
            ctx.restore();
        } else {
            ctx.fillStyle = player.isRolling ? '#000080' : '#0000FF'; 
            ctx.beginPath(); ctx.arc(player.x + player.width/2, player.y + player.height/2, player.width/2, 0, Math.PI * 2); ctx.fill();
        }
        ctx.globalAlpha = 1.0;
        ctx.restore();

        ctx.fillStyle = '#000000'; ctx.font = 'bold 20px Arial'; ctx.textAlign = 'left';
        ctx.fillText(`Время: ${timeElapsed} сек`, 20, 30);
        ctx.fillText(`Кольца: ${player.ringsCollected}`, 20, 60);
        ctx.fillText(`Скорость: ${player.dx.toFixed(1)}`, 20, 90);
        
        if (player.charge > 0) {
            ctx.fillStyle = '#ffcc00';
            ctx.fillRect(20, 110, (player.charge / 20) * 100, 10);
            ctx.strokeStyle = '#000';
            ctx.strokeRect(20, 110, 100, 10);
        }
    }

    function gameLoop() { update(); draw(); requestAnimationFrame(gameLoop); }

    function finishGame() {
        gameState = 'win';
        fetch('{{ route('game.save') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ time: parseFloat(timeElapsed), rings: player.ringsCollected })
        }).then(res => res.json()).then(data => {
            if (data.success) {
                // Ничего не делаем, остаемся на экране победы
            }
        });
    }

    gameLoop();
</script>
@endsection