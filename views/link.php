<?php
/* @var int $pluginId */
/* @var string $serverName */
/* @var string $serverLink */
/* @var string $inviteLink */
/* @var string $authKey */
/* @var ?bool $success */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Link Guilded account</title>
    <!--suppress HtmlUnknownTarget -->
    <link href="/plugin/guilded/style.css" rel=" stylesheet">
</head>
<body>
<h1>Link Guilded account</h1>

<?php if ($success !== true) { ?>
    Join the <a href="<?= $serverLink ?>" target="_blank"><?= $serverName ?></a> server
    <?php if ($inviteLink) { ?>
        (<a href="<?= $inviteLink ?>" target="_blank">invitation link</a>)
    <?php } ?>
    and post a message in the "auth" channel with this text:<br>
    <br>
    <code><?= $authKey ?></code>

    <br>
    <br>
    After you posted it, click the "Check message" button below.<br>
    <br>
    <form action="/plugin/<?= $pluginId ?>/check-message" method="get">
        <input type="submit" value="Check message">
    </form>
<?php } else { ?>
    <strong>Success.</strong>
<?php } ?>

<?php if ($success === false) { ?>
    <br>
    <strong>Error, message not found.</strong>
<?php } ?>

<br>
<br>
<a href="/#Service/<?= $pluginId ?>">Return to Neucore</a>.

</body>
</html>
