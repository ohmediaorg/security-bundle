<h1><?= $plural['readable'] ?></h1>

{% if is_granted(attributes.create, new_<?= $singular['snake_case'] ?>) %}
<a href="{{ path('<?= $singular['snake_case'] ?>_create') }}">Create <?= $singular['readable'] ?></a>
{% endif %}

<table>
  <thead>
    <tr>
      <th><?= $singular['readable'] ?></th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    {% for <?= $singular['snake_case'] ?> in <?= $plural['snake_case'] ?> %}
    <tr>
      <td>{{ <?= $singular['snake_case'] ?> }}</td>
      <td>
<?php if ($has_view_route) { ?>
        {% if is_granted(attributes.view, <?= $singular['snake_case'] ?>) %}
        <a href="{{ path('<?= $singular['snake_case'] ?>_view', {id: <?= $singular['snake_case'] ?>.id}) }}">View</a>
        {% endif %}
<?php } ?>
        {% if is_granted(attributes.edit, <?= $singular['snake_case'] ?>) %}
        <a href="{{ path('<?= $singular['snake_case'] ?>_edit', {id: <?= $singular['snake_case'] ?>.id}) }}">Edit</a>
        {% endif %}
        {% if is_granted(attributes.delete, <?= $singular['snake_case'] ?>) %}
        <a href="{{ path('<?= $singular['snake_case'] ?>_delete', {id: <?= $singular['snake_case'] ?>.id}) }}">Delete</a>
        {% endif %}
      </td>
    </tr>
    {% endfor %}
  </tbody>
</table>
