<form method="post">
  {% if error %}
    <div>{{ error.messageKey|trans(error.messageData, 'security') }}</div>
  {% endif %}

  <label for="inputEmail">Email</label>
  <input type="email" value="{{ last_username }}" name="email" id="inputEmail" required autofocus>

  <label for="inputPassword">Password</label>
  <input type="password" name="password" id="inputPassword" required>

  <input type="hidden" name="_csrf_token" value="{{ csrf_token('authenticate') }}">

  <input type="submit" value="Sign In" />
</form>
