<?php

#
# r/place clone written in a single PHP file.
# Written by Axcelott—Unlicensed.
#

$dimensions = 20;
$default_color = "#ffffff";
$store = "canvas.json";

if(!file_exists($store)) {
  $canvas = initialize_canvas();
  save_canvas($canvas);
} else {
  $canvas = get_canvas();
}

if(isset($_POST["x"]) && isset($_POST["y"]) && isset($_POST["c"])) {
  $x = $_POST["x"];
  $y = $_POST["y"];
  $c = $_POST["c"];

  $canvas[$x][$y] = $c;
  save_canvas($canvas);
}

function initialize_canvas() {
  global $dimensions, $default_color;
  $canvas = [];

  for($x = 0; $x < $dimensions; $x++) {
    for($y = 0; $y < $dimensions; $y++) {
      $canvas[$x][$y] = $default_color;
    }
  }

  return $canvas;
}

function get_canvas() {
  global $store;
  return json_decode(file_get_contents($store));
}

function save_canvas($canvas) {
  global $store;
  return file_put_contents($store, json_encode($canvas));
}

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="color-scheme" content="dark light">

    <title>r/place</title>

    <style>
      body,
      html {
        box-sizing: border-box;
      }

      *,
      :after,
      :before {
        box-sizing: inherit;
      }

      html {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      body {
        font-size: clamp(0.9rem, 2vw, 1.2rem);
        line-height: 1.5;
        font-family: serif;
        max-width: fit-content;
        margin: 0 auto;
      }

      header {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: baseline;
      }

      footer {
        text-align: center;
        font-size: .85em;
        opacity: .8;
        margin-top: 1.5em;
      }

      h1 {
        margin-top: 0;
      }

      table {
        border-collapse: collapse;
        margin: 0 auto;
      }

      td {
        width: 1.6em;
        height: 1.6em;
        border: 1px solid #ccc;
      }

      .cooldown:not(:empty)::before { content: "("; }
      .cooldown:not(:empty)::after { content: ")"; }

      .cooldown {
        font-weight: normal;
        font-size: .55em;
        opacity: .8;
      }
    </style>
  </head>
  <body>
    <header>
      <h1>r/place <span class="cooldown"></span></h1>

      <form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post">
        <input type="hidden" name="x">
        <input type="hidden" name="y">
        <input type="color" name="c" value="<?= $_POST["c"] ?? $default_color ?>">
      </form>
    </header>

    <main>
      <table>
        <?php
          for($x = 0; $x < sizeof($canvas); $x++) {
            $row = $canvas[$x];

            echo "<tr>";
              for($y = 0; $y < sizeof($row); $y++) {
                $pixel = $row[$y];
                echo "<td data-x='$x' data-y='$y' style='background: $pixel'></td>";
              }
            echo "</tr>";
          }
        ?>
      </table>
    </main>

    <script>
      const cooldown = 10;

      const display = document.querySelector(".cooldown");
      const pixels = document.querySelectorAll("td");

      // Fancy timer at the top of the screen counting down.
      let timer = cooldown - timeSinceLastMove();
      if(timer < 0) timer = 0;

      function tick() {
        if(timer > 0) {
          display.innerText = timer;
          timer -= 1;
        }
        else display.innerText = "";
      }

      tick();
      window.setInterval(tick, 1000);

      pixels.forEach(pixel => {
        pixel.addEventListener("click", (e) => {
          if(timeSinceLastMove() >= cooldown) {
            const x = pixel.getAttribute("data-x");
            const y = pixel.getAttribute("data-y");

            resetCooldown();
            setPixel(x, y);
          }
        });
      });

      function timeSinceLastMove() {
        const lastMove = new Date(localStorage.getItem("lastMove"));
        const difference = Date.now() - lastMove.getTime();

        // Time difference is in milliseconds.
        return Math.floor(difference / 1000);
      }

      function resetCooldown() {
        localStorage.setItem("lastMove", new Date());        
      }

      function setPixel(x, y) {
        document.querySelector("[name='x']").value = x;
        document.querySelector("[name='y']").value = y;
        document.querySelector("form").submit();
      }
    </script>

    <footer>
      <p>
        Built with love at <a href="//dupunkto.org">{du}punkto</a>. 
        All wrongs deserved. This site is not affiliated with Reddit.
      </p>
    </footer>
  </body>
</html>
