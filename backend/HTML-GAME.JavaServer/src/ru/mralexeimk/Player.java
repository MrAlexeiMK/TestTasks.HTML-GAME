package ru.mralexeimk;

public class Player {
    private String name;
    private int hp;
    private int damage;
    private Player opponent;
    private int timer;
    private String log;

    public Player(String name, int hp, int damage) {
        this.name = name;
        this.hp = hp;
        this.damage = damage;
        this.opponent = null;
        this.timer = 0;
        this.log = "";
    }

    public void hit() {
        int nhp = opponent.getHp() - damage;
        opponent.setHp(nhp);
        if(nhp <= 0) {
            addRow("Вы убили "+opponent.getName());
            opponent.addRow("Вас убил " + getName() + " :(");
        }
        else {
            addRow("Вы ударили " + opponent.getName() + " на " + damage + " урона");
            opponent.addRow(getName() + " ударил вас на " + damage + " урона");
        }
    }

    public String getLog() {
        return log;
    }

    public void addRow(String row) {
        log += row+"\n";
    }

    public Player getOpponent() {
        return opponent;
    }

    public void setOpponent(Player opponent) {
        this.opponent = opponent;
        this.timer = 30;
    }

    public int getTimer() { return timer; }

    public void minusTimer() {
        timer--;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public int getHp() {
        return hp;
    }

    public void setHp(int hp) {
        this.hp = hp;
        if(hp < 0) this.hp = 0;
    }

    public int getDamage() {
        return damage;
    }

    public void setDamage(int damage) {
        this.damage = damage;
    }
}