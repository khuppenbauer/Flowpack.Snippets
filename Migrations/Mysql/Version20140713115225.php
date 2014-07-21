<?php
namespace TYPO3\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
	Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20140713115225 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema) {
		// this up() migration is autogenerated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
		
		$this->addSql("ALTER TABLE flowpack_snippets_domain_model_comment DROP emailaddress, CHANGE author author VARCHAR(40) DEFAULT NULL");
		$this->addSql("ALTER TABLE flowpack_snippets_domain_model_comment ADD CONSTRAINT FK_CBD3EDA5BDAFD8C8 FOREIGN KEY (author) REFERENCES flowpack_snippets_domain_model_user (persistence_object_identifier)");
		$this->addSql("CREATE INDEX IDX_CBD3EDA5BDAFD8C8 ON flowpack_snippets_domain_model_comment (author)");
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema) {
		// this down() migration is autogenerated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
		
		$this->addSql("ALTER TABLE flowpack_snippets_domain_model_comment DROP FOREIGN KEY FK_CBD3EDA5BDAFD8C8");
		$this->addSql("DROP INDEX IDX_CBD3EDA5BDAFD8C8 ON flowpack_snippets_domain_model_comment");
		$this->addSql("ALTER TABLE flowpack_snippets_domain_model_comment ADD emailaddress VARCHAR(255) NOT NULL, CHANGE author author VARCHAR(80) NOT NULL");
	}
}