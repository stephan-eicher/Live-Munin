pipeline {
  agent any
  stages {
    stage('test code') {
      steps {
        echo 'Testing.'
        sleep 10
        sh 'tar cvfz test.tar.gz *.php'
        archiveArtifacts 'test.tar.gz'
      }
    }
  }
}