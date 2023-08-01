<h1><?php echo $plural['readable']; ?></h1>

{% if is_granted(attributes.create, new_<?php echo $singular['snake_case']; ?>) %}
<a href="{{ path('<?php echo $singular['snake_case']; ?>_create') }}">Create <?php echo $singular['readable']; ?></a>
{% endif %}

<table>
  <thead>
    <tr>
      <th><?php echo $singular['readable']; ?></th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    {% for <?php echo $singular['snake_case']; ?> in <?php echo $plural['snake_case']; ?> %}
    <tr>
      <td>{{ <?php echo $singular['snake_case']; ?> }}</td>
      <td>
<?php if ($has_view_route) { ?>
        {% if is_granted(attributes.view, <?php echo $singular['snake_case']; ?>) %}
        <a href="{{ path('<?php echo $singular['snake_case']; ?>_view', {id: <?php echo $singular['snake_case']; ?>.id}) }}">View</a>
        {% endif %}
<?php } ?>
        {% if is_granted(attributes.edit, <?php echo $singular['snake_case']; ?>) %}
        <a href="{{ path('<?php echo $singular['snake_case']; ?>_edit', {id: <?php echo $singular['snake_case']; ?>.id}) }}">Edit</a>
        {% endif %}
        {% if is_granted(attributes.delete, <?php echo $singular['snake_case']; ?>) %}
        <a href="{{ path('<?php echo $singular['snake_case']; ?>_delete', {id: <?php echo $singular['snake_case']; ?>.id}) }}">Delete</a>
        {% endif %}
      </td>
    </tr>
    {% else %}
    <tr><td colspan="100%">No <?php echo $plural['readable']; ?> found.</td></tr>
    {% endfor %}
  </tbody>
</table>
