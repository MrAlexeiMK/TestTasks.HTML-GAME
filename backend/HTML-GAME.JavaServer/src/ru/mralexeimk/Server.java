package ru.mralexeimk;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.PrintWriter;
import java.net.*;
import java.util.*;

public class Server {
    private ServerSocket serverSocket;
    private int port;
    private HashMap<String, Player> player_by_name;
    private Set<String> ready_players;

    public Server(int port) {
        this.port = port;
        this.player_by_name = new HashMap<>();
        this.ready_players = new HashSet<>();
    }

    public Player getPlayer(String p_name) {
        return player_by_name.get(p_name);
    }

    public void addPlayer(Player p) {
        if(player_by_name.containsKey(p.getName())) {
            player_by_name.replace(p.getName(), p);
        }
        else {
            player_by_name.put(p.getName(), p);
        }
        ready_players.add(p.getName());
    }

    public void removePlayer(Player p) {
        if(player_by_name.containsKey(p.getName())) player_by_name.remove(p.getName());
        if(ready_players.contains(p.getName())) ready_players.remove(p.getName());
    }

    public void removePlayer(String p_name) {
        if(player_by_name.containsKey(p_name)) player_by_name.remove(p_name);
        if(ready_players.contains(p_name)) ready_players.remove(p_name);
    }

    public void addBattle(Player p1, Player p2) {
        p1.setOpponent(p2);
        p2.setOpponent(p1);
        ready_players.remove(p1.getName());
        ready_players.remove(p2.getName());
    }

    public Player getRandomOpponent(String p_name) {
        List<String> players = new ArrayList<>();
        players.addAll(ready_players);
        players.remove(p_name);
        if(players.size() == 0) return null;
        int rand = new Random().nextInt(players.size());
        return getPlayer(players.get(rand));
    }

    public boolean checkPlayer(String p_name) {
        return player_by_name.containsKey(p_name);
    }

    public void start() {
        try {
            serverSocket = new ServerSocket(port);
            while (true) {
                new ClientHandler(serverSocket.accept()).start();
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void stop() {
        try {
            serverSocket.close();
        } catch(Exception e) {
            e.printStackTrace();
        }
    }

    private class ClientHandler extends Thread {
        private Socket clientSocket;
        private PrintWriter out;
        private BufferedReader in;

        public ClientHandler(Socket socket) {
            this.clientSocket = socket;
        }

        public void run() {
            try {
                out = new PrintWriter(clientSocket.getOutputStream(), true);
                in = new BufferedReader(new InputStreamReader(clientSocket.getInputStream()));
            } catch (IOException e) {}

            String inputLine;
            try {
                if ((inputLine = in.readLine()) != null) {
                    String[] spl = inputLine.split("\\|");
                    if (spl[0].equals("USER_ADD")) {
                        Player pl = new Player(spl[1], Integer.parseInt(spl[2]), Integer.parseInt(spl[3]));
                        if(!player_by_name.containsKey(pl.getName())) {
                            addPlayer(pl);
                            System.out.println("User added: " + spl[1]);
                        }
                    }
                    else if (spl[0].equals("USER_REMOVE")) {
                        String username = spl[1];
                        Player p = getPlayer(username);
                        if(p.getOpponent() != null) p.getOpponent().setOpponent(null);
                        p.setOpponent(null);
                        removePlayer(username);
                        System.out.println("User removed: " + username);
                    }
                    else if (spl[0].equals("USER_CHECK")) {
                        if(spl.length == 2) {
                            String username = spl[1];
                            boolean ans = checkPlayer(username);
                            System.out.println("User " + username + " was checked (" + ans + ")");
                            out.println(ans);
                        }
                        else out.println("false");
                        out.flush();
                    }
                    else if(spl[0].equals("USER_OPPONENT")) {
                        String username = spl[1];
                        Player player = getPlayer(username);
                        while(player == null) {
                            player = getPlayer(username);
                        }
                        if(player.getOpponent() == null) {
                            Player opponent = getRandomOpponent(username);
                            if (opponent == null) {
                                out.println("null");
                                out.flush();
                            } else {
                                addBattle(player, opponent);
                                System.out.println("Opponent for " + username + " found: " + opponent.getName());
                                out.println(opponent.getName() + "|" + opponent.getHp() + "|" + opponent.getDamage()
                                + "|" + opponent.getTimer());
                                out.flush();

                                while(true) {
                                    if(opponent == null || opponent.getTimer() <= 0 || player.getOpponent() == null) break;
                                    if(player == null || player.getTimer() <= 0 || opponent.getOpponent() == null) break;
                                    opponent.minusTimer();
                                    player.minusTimer();
                                    try {
                                        sleep(1000);
                                    } catch (InterruptedException e) {}
                                }
                            }
                        }
                        else {
                            Player opponent = player.getOpponent();
                            out.println(opponent.getName() + "|" + opponent.getHp() + "|" + opponent.getDamage()
                            + "|" + opponent.getTimer());
                            out.flush();
                        }
                    }
                    else if(spl[0].equals("HIT_OPPONENT")) {
                        String username = spl[1];
                        Player player = getPlayer(username);
                        if(player != null) {
                            Player opponent = player.getOpponent();
                            if(opponent != null) {
                                player.hit();
                                System.out.println(username + " hit " + opponent.getName() + " (hp: "+opponent.getHp()+")");
                                if(opponent.getHp() == 0) {
                                    try {
                                        sleep(3000);
                                    } catch (InterruptedException e) {}
                                    player.setOpponent(null);
                                    opponent.setOpponent(null);
                                    removePlayer(player);
                                    removePlayer(opponent);
                                }
                            }
                        }
                    }
                    else if(spl[0].equals("GET_HP")) {
                        String username = spl[1];
                        Player player = getPlayer(username);
                        if(player == null) {
                            out.println("null");
                        }
                        else {
                            out.println(player.getHp());
                        }
                        out.flush();
                    }
                    else if(spl[0].equals("GET_LOG")) {
                        String username = spl[1];
                        Player player = getPlayer(username);
                        if(player == null) {
                            out.println("null");
                        }
                        else {
                            out.println(player.getLog());
                        }
                        out.flush();
                    }
                    else if(spl[0].equals("IS_OPPONENT")) {
                        String username1 = spl[1];
                        if(spl.length == 3) {
                            String username2 = spl[2];
                            Player p1 = getPlayer(username1);
                            Player p2 = getPlayer(username2);
                            if (p1 != null && p2 != null &&
                                    p1.getOpponent() != null &&
                                    p2.getOpponent() != null &&
                                    p1.getOpponent().equals(p2) &&
                                    p2.getOpponent().equals(p1)) out.println("true");
                            else out.println("false");
                        }
                        else out.println("false");
                        out.flush();
                    }
                }
            } catch (IOException e) {}

            try {
                in.close();
                out.close();
                clientSocket.close();
            } catch (IOException e) {}
        }
    }
}