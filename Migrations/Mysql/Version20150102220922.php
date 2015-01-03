<?php
namespace TYPO3\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
	Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20150102220922 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema) {
		// this up() migration is autogenerated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("CREATE TABLE flowpack_snippets_domain_model_notification (persistence_object_identifier VARCHAR(40) NOT NULL, target VARCHAR(40) DEFAULT NULL, post VARCHAR(40) DEFAULT NULL, source VARCHAR(40) DEFAULT NULL, timestamp DATETIME NOT NULL, type VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, INDEX IDX_FF54A8D2466F2FFC (target), INDEX IDX_FF54A8D25A8A6C8D (post), INDEX IDX_FF54A8D25F8A7F73 (source), PRIMARY KEY(persistence_object_identifier)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
		$this->addSql("ALTER TABLE flowpack_snippets_domain_model_notification ADD CONSTRAINT FK_FF54A8D2466F2FFC FOREIGN KEY (target) REFERENCES flowpack_snippets_domain_model_user (persistence_object_identifier)");
		$this->addSql("ALTER TABLE flowpack_snippets_domain_model_notification ADD CONSTRAINT FK_FF54A8D25A8A6C8D FOREIGN KEY (post) REFERENCES flowpack_snippets_domain_model_post (persistence_object_identifier)");
		$this->addSql("ALTER TABLE flowpack_snippets_domain_model_notification ADD CONSTRAINT FK_FF54A8D25F8A7F73 FOREIGN KEY (source) REFERENCES flowpack_snippets_domain_model_user (persistence_object_identifier)");
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema) {
		// this down() migration is autogenerated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("DROP TABLE flowpack_snippets_domain_model_notification");
	}
}