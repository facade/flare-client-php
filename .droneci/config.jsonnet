local Pipeline(phpVersion, composerFlags = "") = {
  kind: "pipeline",
  name: "php "+phpVersion+" "+composerFlags,
  steps: [
      {
         name: "composer install",
        image: "composer",
        environment: {
            SSH_KEY: { from_secret: "SSH_KEY" },
        },
        commands: [
           "mkdir /root/.ssh",
           "eval $(ssh-agent)",
           "echo \"$SSH_KEY\"",
           "( echo \"$SSH_KEY\" | ssh-add - ) || (echo 'Broken SSH key.' && exit 1)",
           "touch /root/.ssh/known_hosts",
           "chmod 600 /root/.ssh/known_hosts",
           "ssh-keyscan -H github.com > /etc/ssh/ssh_known_hosts 2> /dev/null",
           "composer update --no-interaction --prefer-source " + composerFlags,
        ],
      },
    {
      name: "run tests",
      image: "php:"+phpVersion,
      commands: [
        "vendor/bin/phpunit",
      ]
    }
  ]
};

[
  Pipeline("7.1"),
  Pipeline("7.1", "--prefer-lowest"),
  Pipeline("7.2"),
  Pipeline("7.2", "--prefer-lowest"),
  Pipeline("7.3"),
  Pipeline("7.3", "--prefer-lowest"),
]
