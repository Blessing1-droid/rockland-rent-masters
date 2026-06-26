<?php

$flash = getFlashMessage();

if ($flash):
?>

<div class="rrm-alert rrm-alert-<?= e($flash['type']) ?>">
    <div class="rrm-alert-content">
        <?= e($flash['message']) ?>
    </div>

```
<button
    type="button"
    class="rrm-alert-close"
    aria-label="Close Alert">
    &times;
</button>
```

</div>

<?php endif; ?>

<?php if (!empty($errors) && is_array($errors)): ?>

```
<?php foreach ($errors as $error): ?>

    <div class="rrm-alert rrm-alert-error">
        <div class="rrm-alert-content">
            <?= e($error) ?>
        </div>

        <button
            type="button"
            class="rrm-alert-close"
            aria-label="Close Alert">
            &times;
        </button>
    </div>

<?php endforeach; ?>
```

<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {

    document
        .querySelectorAll('.rrm-alert-close')
        .forEach(button => {

            button.addEventListener('click', () => {

                button.parentElement.classList.add('hide');

                setTimeout(() => {
                    button.parentElement.remove();
                }, 300);

            });

        });

    document
        .querySelectorAll('.rrm-alert')
        .forEach(alert => {

            setTimeout(() => {

                alert.classList.add('hide');

                setTimeout(() => {
                    alert.remove();
                }, 300);

            }, 5000);

        });

});
</script>

?>
