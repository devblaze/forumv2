version = "2023.04"

project {
    // If your repository is already configured as a VCS Root via TeamCity's UI,
    // you can reference it here by its ID, e.g. "MyProject_GitRepository"
    vcsRoot(MyProject_GitRepository)

    buildType(LaravelSailBuild)
}

// Define a build type (pipeline) for running tests
object LaravelSailBuild : BuildType({
    name = "Laravel Sail CI"

    // Link to the VCS Root
    vcs {
        root(MyProject_GitRepository)
    }

    // Configure your build steps:
    steps {
        // 1. Pull down code (TeamCity does the checkout automatically if VCS is defined)

        // 2. (Optional) Show docker version or set up Docker before using it
        script {
            name = "Set up Docker"
            scriptContent = """
                docker --version
            """.trimIndent()
        }

        // 3. Install Composer dependencies
        script {
            name = "Install Composer Dependencies"
            scriptContent = """
                composer install
            """.trimIndent()
        }

        // 4. Install Node dependencies
        script {
            name = "Install NPM Dependencies"
            scriptContent = """
                npm install
            """.trimIndent()
        }

        // 5. Spin up environment with Laravel Sail
        script {
            name = "Startup Laravel Sail"
            scriptContent = """
                cp .env.example .env
                ./vendor/bin/sail up -d
            """.trimIndent()
        }

        // 6. Run Tests
        script {
            name = "Run Tests"
            scriptContent = """
                ./vendor/bin/sail test
            """.trimIndent()
        }
    }

    // (Optional) If you want to pass environment variables, you can do so here:
    requirements {
        // For example, ensure Docker is available
        contains("docker.server.osType", "linux")
    }
})