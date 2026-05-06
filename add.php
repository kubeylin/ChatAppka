<!DOCTYPE html>
<html>
<head>
    <title>Pridať hru</title>
</head>
<body>

<h2>Pridať hru</h2>

<form method="POST">
    <input type="text" name="name" placeholder="Názov hry"><br>
    <input type="text" name="genre" placeholder="Žáner"><br>
    <input type="text" name="platform" placeholder="Platforma"><br>
    
    <select name="status">
        <option value="playing">Hrám</option>
        <option value="played">Dohraté</option>
        <option value="wishlist">Wishlist</option>
    </select><br>

    <button type="submit">Uložiť</button>
</form>

<a href="index.php">Späť</a>

</body>
</html>