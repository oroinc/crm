<?php include 'includes/head.php'; ?>
<body>
<div id="page">
    <div id="top-page">
        <?php include 'includes/header-simple.php'; ?>
        <div class="container">
            <form class="form-signin">
                <h2 class="form-signin-heading">Please sign in</h2>
                <input type="text" placeholder="Email address" class="input-block-level">
                <input type="password" placeholder="Password" class="input-block-level">
                <div class="form-row">
                    <label class="checkbox">
                        <input type="checkbox" value="remember-me"> Remember me
                    </label>
                </div>
                <button type="submit" class="btn btn-large btn-primary">Sign in</button>
            </form>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>