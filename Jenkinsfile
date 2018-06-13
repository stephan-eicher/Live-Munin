pipeline {
  agent any
  stages {
    stage('test code') {
      steps {
        echo 'Testing.'
        timestamps()
        sleep 10
        sh 'tar cvfz test.tar.gz *.php'
        archiveArtifacts 'test.tar.gz'
      }
    }
  }
}