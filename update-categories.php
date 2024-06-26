<?php
require_once('wp-load.php');

$success_messages = [];

function update_single_post_category($post_id, $child_category_id) {
    wp_set_post_categories($post_id, array($child_category_id));
    return "Les catégories de l'article <a href='" . get_permalink($post_id) . "' target='_blank'>ID $post_id</a> ont été mises à jour avec succès !";
}

function update_multiple_posts_categories($post_ids) {
    $messages = [];
    foreach ($post_ids as $post_id) {
        $post_id = intval($post_id);
        if (isset($_POST['child_category_id_' . $post_id])) {
            $child_category_id = intval($_POST['child_category_id_' . $post_id]);
            wp_set_post_categories($post_id, array($child_category_id));
            $messages[] = "Les catégories de l'article <a href='" . get_permalink($post_id) . "' target='_blank'>ID $post_id</a> ont été mises à jour avec succès !";
        }
    }
    return $messages;
}

if (isset($_POST['bulk_submit'])) {
    if (isset($_POST['post_ids'])) {
        $success_messages = array_merge($success_messages, update_multiple_posts_categories($_POST['post_ids']));
    }
}

if (isset($_POST['single_submit'])) {
    $post_id = intval($_POST['post_id']);
    $child_category_id = intval($_POST['child_category_id']);
    $success_messages[] = update_single_post_category($post_id, $child_category_id);
}

$args = array(
    'post_type' => 'post',
    'posts_per_page' => -1,
    'category_name' => 'ives-conference-series'
);
$all_posts = get_posts($args);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Mise à jour des catégories</title>
    <script>
        function toggleSelectAll(source) {
            checkboxes = document.getElementsByName('post_ids[]');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
    </script>
</head>
<body>
    <h1>Mise à jour des catégories des posts</h1>
    <?php if (!empty($success_messages)): ?>
        <div style="margin-bottom: 20px; padding: 10px; border: 1px solid #4CAF50; background-color: #d4edda;">
            <?php foreach ($success_messages as $message): ?>
                <p><?php echo $message; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <input type="checkbox" onClick="toggleSelectAll(this)"> Tout sélectionner<br><br>
        <input type="submit" name="bulk_submit" value="Mettre à jour la catégorie des Posts sélectionnés"><br><br>

        <?php foreach ($all_posts as $post): ?>
            <?php 
            $categories = get_the_category($post->ID);
            if (count($categories) > 1):
            ?>
                <div style="margin-bottom: 20px; padding: 10px; border: 1px solid #ccc;">
                    <input type="checkbox" name="post_ids[]" value="<?php echo $post->ID; ?>">
                    <h2>
                        <a href="<?php echo get_permalink($post->ID); ?>" target="_blank">
                            <?php echo $post->post_title; ?> (ID: <?php echo $post->ID; ?>)
                        </a>
                    </h2>
                    <p>Catégories actuelles :
                        <?php 
                        $category_names = array();
                        foreach ($categories as $category) {
                            $category_names[] = $category->name;
                        }
                        echo implode(', ', $category_names);
                        ?>
                    </p>
                    
                    <?php
                    $child_category = null;
                    foreach ($categories as $category) {
                        if ($child_category == null || term_is_ancestor_of($child_category->term_id, $category->term_id, 'category')) {
                            $child_category = $category;
                        }
                    }
                    ?>
                    
                    <p>Catégorie enfant trouvée : <strong><?php echo $child_category->name; ?></strong></p>

                    <input type="hidden" name="child_category_id_<?php echo $post->ID; ?>" value="<?php echo $child_category->term_id; ?>">
                    
                    <button type="submit" name="single_submit" value="Mettre à jour la catégorie de ce Post">Mettre à jour la catégorie de ce Post</button>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </form>
</body>
</html>
